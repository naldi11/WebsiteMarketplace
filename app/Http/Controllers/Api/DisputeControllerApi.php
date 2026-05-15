<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\DisputeLog;
use App\Models\Message;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DisputeControllerApi extends Controller
{
    // ============================================================
    // BUYER: Buka dispute (laporan masalah)
    // POST /api/disputes/{transactionId}
    // ============================================================
    public function openDispute(Request $request, $transactionId)
    {
        $user = $request->user();

        $transaction = Transaction::with(['buyer', 'seller'])
            ->where('buyer_id', $user->id)
            ->findOrFail($transactionId);

        if ($transaction->status !== 'shipped') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Laporan masalah hanya bisa dibuka ketika pesanan dalam status dikirim.',
            ], 400);
        }

        if (Dispute::where('transaction_id', $transactionId)
                ->whereNotIn('status', ['closed'])
                ->exists()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Dispute untuk pesanan ini sudah ada.',
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'reason'      => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'photos'      => 'nullable|array|max:5',
            'photos.*'    => 'file|mimes:jpg,jpeg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            // Upload foto bukti
            $photoPaths = [];
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $photoPaths[] = $photo->store('dispute_evidence', 'public');
                }
            }

            // Buat dispute
            $dispute = Dispute::create([
                'transaction_id'           => $transaction->id,
                'buyer_id'                 => $user->id,
                'seller_id'                => $transaction->seller_id,
                'reason'                   => $request->reason,
                'description'              => $request->description,
                'evidence_photos'          => $photoPaths,
                'status'                   => 'open',
                'conversation_with_user_id'=> $transaction->seller_id,
            ]);

            // Update status transaksi
            $transaction->update([
                'status'      => 'disputed',
                'disputed_at' => now(),
            ]);

            // Log
            $dispute->addLog('buyer', $user->id, 'dispute_opened',
                "Pembeli membuka laporan: {$request->reason}",
                ['transaction_id' => $transaction->id, 'amount' => $transaction->total_amount]
            );

            // Kirim pesan ke chat yang sudah ada (atau buat otomatis)
            $this->sendDisputeMessageToChat($transaction, $user, $dispute);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Laporan masalah berhasil dibuat. Admin akan segera meninjau.',
                'data'    => $dispute->load('logs'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("DisputeOpen Error #{$transactionId}: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal membuat laporan: ' . $e->getMessage()], 500);
        }
    }

    // ============================================================
    // BUYER/SELLER: Lihat detail dispute
    // GET /api/disputes/{transactionId}
    // ============================================================
    public function show(Request $request, $transactionId)
    {
        $user = $request->user();

        $dispute = Dispute::with(['buyer', 'seller', 'resolvedBy', 'logs.actor'])
            ->where('transaction_id', $transactionId)
            ->where(function ($q) use ($user) {
                $q->where('buyer_id', $user->id)
                  ->orWhere('seller_id', $user->id);
            })
            ->firstOrFail();

        return response()->json(['status' => 'success', 'data' => $dispute]);
    }

    // ============================================================
    // ADMIN: Daftar semua dispute aktif
    // GET /api/admin/disputes
    // ============================================================
    public function adminIndex(Request $request)
    {
        $disputes = Dispute::with(['transaction', 'buyer', 'seller', 'logs'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json(['status' => 'success', 'data' => $disputes]);
    }

    // ============================================================
    // ADMIN: Resolusi dispute
    // POST /api/admin/disputes/{id}/resolve
    // body: { winner: "buyer"|"seller", admin_notes: "..." }
    // ============================================================
    public function adminResolve(Request $request, $disputeId)
    {
        $admin = $request->user();

        $validator = Validator::make($request->all(), [
            'winner'      => 'required|in:buyer,seller',
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $dispute = Dispute::with('transaction')->findOrFail($disputeId);

        if (!in_array($dispute->status, ['open', 'admin_reviewing'])) {
            return response()->json(['status' => 'error', 'message' => 'Dispute sudah diproses sebelumnya.'], 400);
        }

        DB::beginTransaction();
        try {
            $dispute->update([
                'status'           => $request->winner === 'buyer' ? 'buyer_won' : 'seller_won',
                'winner'           => $request->winner,
                'resolved_by'      => $admin->id,
                'admin_notes'      => $request->admin_notes,
                'admin_reviewed_at'=> now(),
            ]);

            $dispute->addLog('admin', $admin->id, 'admin_resolved',
                "Admin memutuskan pemenang: {$request->winner}. Catatan: {$request->admin_notes}",
                ['winner' => $request->winner]
            );

            if ($request->winner === 'seller') {
                // Langsung selesaikan — lepas dana ke penjual dengan potongan 10%
                $this->releaseFundsToSeller($dispute->transaction, $dispute, $admin->id);

                $dispute->update(['status' => 'closed', 'resolved_at' => now()]);
                $dispute->addLog('system', null, 'dispute_closed_seller_won',
                    'Dana dilepas ke penjual (10% dipotong platform)');
            } else {
                // buyer_won → tunggu pembeli kirim barang balik
                $dispute->addLog('system', null, 'waiting_buyer_return',
                    'Pembeli diminta mengirim kembali barang ke penjual');

                // Kirim notifikasi ke chat
                $this->sendSystemMessageToChat(
                    $dispute->transaction,
                    "✅ Admin telah memutuskan kasus ini berpihak pada PEMBELI. " .
                    "Silakan kembalikan barang ke penjual dan isi nomor resi pengiriman balik."
                );
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Dispute berhasil diputuskan.',
                'data'    => $dispute->fresh(['logs']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("AdminResolve Error #{$disputeId}: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ============================================================
    // BUYER: Konfirmasi sudah kirim barang balik
    // POST /api/disputes/{id}/buyer-ship-back
    // body: { return_courier, return_tracking_number }
    // ============================================================
    public function buyerShipBack(Request $request, $disputeId)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'return_courier'          => 'required|string|max:100',
            'return_tracking_number'  => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $dispute = Dispute::where('buyer_id', $user->id)
            ->where('status', 'buyer_won')
            ->findOrFail($disputeId);

        $dispute->update([
            'status'                 => 'buyer_shipping_back',
            'return_courier'         => $request->return_courier,
            'return_tracking_number' => $request->return_tracking_number,
            'buyer_shipped_back_at'  => now(),
        ]);

        $dispute->addLog('buyer', $user->id, 'buyer_shipped_back',
            "Pembeli mengirim barang balik via {$request->return_courier}",
            [
                'courier'  => $request->return_courier,
                'tracking' => $request->return_tracking_number,
            ]
        );

        $this->sendSystemMessageToChat(
            $dispute->transaction,
            "📦 Pembeli telah mengirim barang kembali.\n" .
            "Kurir: {$request->return_courier}\n" .
            "No. Resi: {$request->return_tracking_number}\n\n" .
            "Penjual harap konfirmasi penerimaan barang."
        );

        return response()->json(['status' => 'success', 'message' => 'Resi pengiriman balik berhasil disimpan.']);
    }

    // ============================================================
    // SELLER: Konfirmasi terima barang balik → trigger refund
    // POST /api/disputes/{id}/seller-confirm-return
    // ============================================================
    public function sellerConfirmReturn(Request $request, $disputeId)
    {
        $user = $request->user();

        $dispute = Dispute::with('transaction')
            ->where('seller_id', $user->id)
            ->where('status', 'buyer_shipping_back')
            ->findOrFail($disputeId);

        DB::beginTransaction();
        try {
            $dispute->update([
                'status'                   => 'seller_received_back',
                'seller_received_back_at'  => now(),
            ]);

            $dispute->addLog('seller', $user->id, 'seller_confirmed_return',
                'Penjual mengkonfirmasi telah menerima kembali barang dari pembeli'
            );

            // Proses refund otomatis ke pembeli
            $this->processRefundToBuyer($dispute);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Penerimaan barang dikonfirmasi. Refund sedang diproses ke pembeli.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("SellerConfirmReturn Error #{$disputeId}: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ============================================================
    // PRIVATE: Proses refund ke pembeli
    // ============================================================
    private function processRefundToBuyer(Dispute $dispute)
    {
        $transaction = $dispute->transaction;
        $amount      = $transaction->total_amount; // Refund FULL amount ke pembeli

        $buyerWallet  = Wallet::getOrCreate($dispute->buyer_id);
        $sellerWallet = Wallet::getOrCreate($dispute->seller_id);

        // Kembalikan dari pending_balance buyer (escrow) ke balance buyer
        if ($buyerWallet->pending_balance >= $amount) {
            $buyerWallet->pending_balance -= $amount;
            $buyerWallet->save();
        }

        $buyerWallet->credit(
            $amount,
            'refund',
            "Refund Dispute #D{$dispute->id} - Pesanan #{$transaction->id}",
            'dispute',
            $dispute->id
        );

        // Update status
        $dispute->update([
            'status'      => 'refunded',
            'refunded_at' => now(),
            'resolved_at' => now(),
        ]);

        $transaction->update([
            'status'             => 'refunded',
            'funds_released_at'  => now(),
        ]);

        $dispute->addLog('system', null, 'refund_processed',
            "Refund Rp " . number_format($amount, 0, ',', '.') . " berhasil dikreditkan ke wallet pembeli",
            ['amount' => $amount, 'buyer_balance_after' => $buyerWallet->balance]
        );

        // Notifikasi ke chat
        $this->sendSystemMessageToChat(
            $transaction,
            "✅ REFUND BERHASIL!\n" .
            "Rp " . number_format($amount, 0, ',', '.') . " telah dikembalikan ke MeyPay Wallet Anda.\n" .
            "Terima kasih atas kesabaran Anda."
        );
    }

    // ============================================================
    // PRIVATE: Lepas dana ke penjual dengan potongan 10% platform
    // ============================================================
    private function releaseFundsToSeller(Transaction $transaction, Dispute $dispute, ?int $adminId = null)
    {
        $grossAmount  = $transaction->seller_amount;
        $platformFee  = round($grossAmount * 0.10);
        $netToSeller  = $grossAmount - $platformFee;

        $buyerWallet  = Wallet::getOrCreate($transaction->buyer_id);
        $sellerWallet = Wallet::getOrCreate($transaction->seller_id);

        // Kurangi pending_balance buyer
        if ($buyerWallet->pending_balance >= $grossAmount) {
            $buyerWallet->pending_balance -= $grossAmount;
            $buyerWallet->save();
        }

        // Kredit ke penjual (net)
        $sellerWallet->credit(
            $netToSeller,
            'payout',
            "Penjualan #TXN-{$transaction->id} (setelah potongan 10% platform)",
            'transaction',
            $transaction->id
        );

        // Catat pendapatan platform menggunakan method yang ada
        \App\Models\PlatformEarning::recordEarning(
            $transaction->id,
            $platformFee,   // service_fee
            0,              // payment_fee
            "10% service fee dari TXN #{$transaction->id}"
        );

        $transaction->update([
            'status'            => 'completed',
            'funds_released_at' => now(),
        ]);

        if ($dispute) {
            $dispute->addLog(
                $adminId ? 'admin' : 'system',
                $adminId,
                'funds_released_to_seller',
                "Dana Rp " . number_format($netToSeller, 0, ',', '.') . " dilepas ke penjual (dipotong 10% = Rp " . number_format($platformFee, 0, ',', '.') . ")",
                ['gross' => $grossAmount, 'fee' => $platformFee, 'net' => $netToSeller]
            );
        }
    }

    // ============================================================
    // PRIVATE: Kirim pesan sistem ke chat pembeli-penjual
    // ============================================================
    private function sendDisputeMessageToChat(Transaction $transaction, $buyer, Dispute $dispute)
    {
        // Cari pesan antara buyer dan seller yang sudah ada
        // Chat model menggunakan user_id dan other_user_id
        $msg = "⚠️ LAPORAN MASALAH DIBUKA\n\n" .
               "Pembeli melaporkan masalah untuk pesanan #{$transaction->id}.\n" .
               "Alasan: {$dispute->reason}\n\n" .
               "Admin akan segera meninjau kasus ini. Mohon tidak melakukan tindakan apapun sebelum keputusan admin.";

        Message::create([
            'sender_id'    => $buyer->id,
            'receiver_id'  => $transaction->seller_id,
            'message'      => $msg,
        ]);
    }

    private function sendSystemMessageToChat(Transaction $transaction, string $message)
    {
        try {
            Message::create([
                'sender_id'   => $transaction->buyer_id,
                'receiver_id' => $transaction->seller_id,
                'message'     => $message,
            ]);
        } catch (\Exception $e) {
            Log::warning("Failed to send system chat message: " . $e->getMessage());
        }
    }
}
