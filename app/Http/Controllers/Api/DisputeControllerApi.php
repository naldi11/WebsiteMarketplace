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
                $this->releaseFundsToSeller($dispute);

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
                    "[KEPUTUSAN ADMIN] Pembeli dinyatakan menang. Silakan kembalikan barang ke penjual dan input nomor resi di aplikasi."
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
            "[INFO] Pembeli mengirim barang kembali via {$request->return_courier}. Resi: {$request->return_tracking_number}. Penjual harap konfirmasi penerimaan."
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
        DB::transaction(function () use ($dispute) {
            // Lock dispute row to prevent concurrent refund
            $locked = Dispute::lockForUpdate()->find($dispute->id);
            if (!$locked || $locked->status === 'refunded') {
                return; // Already refunded — skip
            }

            $transaction = $locked->transaction;
            $amount      = $transaction->total_amount;

            // Get-or-create wallets, then lock them
            $buyerWalletId  = \App\Models\Wallet::getOrCreate($locked->buyer_id)->id;
            $sellerWalletId = \App\Models\Wallet::getOrCreate($locked->seller_id)->id;

            $buyerWallet  = \App\Models\Wallet::lockForUpdate()->findOrFail($buyerWalletId);
            $sellerWallet = \App\Models\Wallet::lockForUpdate()->findOrFail($sellerWalletId);

            // Release escrow from buyer pending balance
            if ($buyerWallet->pending_balance >= $amount) {
                $buyerWallet->pending_balance -= $amount;
                $buyerWallet->save();
            }

            // Credit refund to buyer wallet
            $buyerWallet->credit(
                $amount,
                'refund',
                "Refund Laporan #D{$locked->id} — Pesanan #{$transaction->id}",
                'dispute',
                $locked->id
            );

            // Mark as refunded
            $locked->update(['status' => 'refunded']);
            $transaction->update(['status' => 'disputed_refunded', 'buyer_can_rate' => false]);

            $locked->addLog('system', null, 'refunded',
                "Dana Rp " . number_format($amount, 0, ',', '.') . " dikembalikan ke pembeli"
            );
        });
    }

    // ============================================================
    // PRIVATE: Lepas dana ke penjual dengan potongan platform
    // ============================================================
    private function releaseFundsToSeller(Dispute $dispute)
    {
        DB::transaction(function () use ($dispute) {
            // Lock dispute row to prevent concurrent release
            $locked = Dispute::lockForUpdate()->find($dispute->id);
            if (!$locked || $locked->status === 'closed') {
                return; // Already closed — skip
            }

            $transaction = $locked->transaction;
            $grossAmount = $transaction->seller_amount;

            $feePercent  = (float) optional(
                \App\Models\SystemSetting::where('key', 'service_fee_percent')->first()
            )->value ?? 10;

            $platformFee = round($grossAmount * $feePercent / 100);
            $netToSeller = $grossAmount - $platformFee;

            // Get-or-create wallets, then lock them
            $buyerWalletId  = \App\Models\Wallet::getOrCreate($locked->buyer_id)->id;
            $sellerWalletId = \App\Models\Wallet::getOrCreate($locked->seller_id)->id;

            $buyerWallet  = \App\Models\Wallet::lockForUpdate()->findOrFail($buyerWalletId);
            $sellerWallet = \App\Models\Wallet::lockForUpdate()->findOrFail($sellerWalletId);

            // Release escrow
            if ($buyerWallet->pending_balance >= $grossAmount) {
                $buyerWallet->pending_balance -= $grossAmount;
                $buyerWallet->save();
            }

            // Credit net amount to seller
            $sellerWallet->credit(
                $netToSeller,
                'payout',
                "Penjualan Laporan #D{$locked->id} (dipotong {$feePercent}% platform)",
                'dispute',
                $locked->id
            );

            // Record platform fee
            \App\Models\PlatformEarning::recordEarning(
                $transaction->id,
                $platformFee,
                0,
                "{$feePercent}% service fee dari Laporan #D{$locked->id}"
            );

            // Close dispute and transaction
            $locked->update(['status' => 'closed']);
            $transaction->update(['status' => 'completed']);

            $locked->addLog('system', null, 'seller_won',
                "Dana Rp " . number_format($netToSeller, 0, ',', '.') . " diteruskan ke penjual"
            );
        });
    }

    // ============================================================
    // PRIVATE: Kirim pesan sistem ke chat pembeli-penjual
    // ============================================================
    private function sendDisputeMessageToChat(Transaction $transaction, $buyer, Dispute $dispute)
    {
        // Cari pesan antara buyer dan seller yang sudah ada
        // Chat model menggunakan user_id dan other_user_id
        $msg = "[LAPORAN MASALAH] Pembeli membuka laporan untuk pesanan #{$transaction->id}. Alasan: {$dispute->reason}. Admin akan segera meninjau.";

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
