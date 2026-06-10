<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\DisputeLog;
use App\Models\Message;
use App\Models\Transaction;
use App\Models\SellerBalance;
use App\Models\RefundRecord;
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
    // SELLER: Daftar semua dispute milik penjual
    // GET /api/seller/disputes
    // ============================================================
    public function sellerIndex(Request $request)
    {
        $user = $request->user();
        
        $disputes = Dispute::with(['transaction', 'buyer', 'logs'])
            ->where('seller_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['status' => 'success', 'data' => $disputes]);
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
                    "Pembeli dinyatakan menang. Silakan kembalikan barang ke penjual dan input nomor resi di aplikasi."
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
            'return_shipping_proof'   => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $dispute = Dispute::where('buyer_id', $user->id)
            ->where('status', 'buyer_won')
            ->findOrFail($disputeId);

        $path = null;
        if ($request->hasFile('return_shipping_proof')) {
            $path = $request->file('return_shipping_proof')->store('disputes/returns', 'public');
        }

        $dispute->update([
            'status'                 => 'buyer_shipping_back',
            'return_courier'         => $request->return_courier,
            'return_tracking_number' => $request->return_tracking_number,
            'return_shipping_proof'  => $path,
            'buyer_shipped_back_at'  => now(),
        ]);

        $dispute->addLog('buyer', $user->id, 'buyer_shipped_back',
            "Pembeli mengirim barang balik via {$request->return_courier}",
            [
                'courier'  => $request->return_courier,
                'tracking' => $request->return_tracking_number,
                'proof'    => $path,
            ]
        );

        $this->sendSystemMessageToChat(
            $dispute->transaction,
            "Pembeli mengirim barang kembali via {$request->return_courier}. Resi: {$request->return_tracking_number}. Penjual harap konfirmasi penerimaan."
        );

        return response()->json(['status' => 'success', 'message' => 'Resi & bukti pengiriman balik berhasil disimpan.']);
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

            // Bebaskan escrow dari SellerBalance
            $transaction  = $dispute->transaction;
            $amount       = $transaction->total_amount;
            $escrowAmount = $transaction->seller_amount;

            $sellerBalance = SellerBalance::where('user_id', $transaction->seller_id)->first();
            if ($sellerBalance) {
                $sellerBalance->pending_balance = max(0, $sellerBalance->pending_balance - $escrowAmount);
                $sellerBalance->save();
            }

            // Restore stock
            foreach ($transaction->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock', $item->quantity);
                }
            }

            // Buat RefundRecord — pencatatan detail (pending)
            $exists = RefundRecord::where('dispute_id', $dispute->id)->first();
            if (!$exists) {
                RefundRecord::createPending(
                    transactionId: $transaction->id,
                    buyerId:       $dispute->buyer_id,
                    amount:        $amount,
                    disputeId:     $dispute->id,
                    notes:         "Refund Laporan #D{$dispute->id} — Pesanan #{$transaction->id}",
                );
            }

            $this->sendSystemMessageToChat(
                $transaction,
                "Penjual mengkonfirmasi penerimaan barang balik. Dana refund sebesar Rp " . number_format($amount, 0, ',', '.') . " akan diproses transfer manual oleh Admin."
            );

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Penerimaan barang dikonfirmasi. Admin akan memproses refund secara manual.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("SellerConfirmReturn Error #{$disputeId}: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ============================================================
    // BUYER: Konfirmasi terima dana refund
    // POST /api/disputes/{id}/buyer-confirm-refund
    // ============================================================
    public function buyerConfirmRefund(Request $request, $id)
    {
        $user = $request->user();

        $dispute = Dispute::with('transaction')
            ->where('buyer_id', $user->id)
            ->where('status', 'refund_transferred')
            ->findOrFail($id);

        DB::beginTransaction();
        try {
            $dispute->update([
                'status'      => 'refunded',
                'refunded_at' => now(),
                'resolved_at' => now(),
            ]);

            $dispute->transaction->update([
                'status'            => 'disputed_refunded',
                'buyer_can_rate'    => false,
                'funds_released_at' => now(),
            ]);

            $dispute->addLog('buyer', $user->id, 'buyer_confirmed_refund',
                'Pembeli mengkonfirmasi telah menerima dana refund'
            );

            $this->sendSystemMessageToChat(
                $dispute->transaction,
                "Pembeli mengkonfirmasi telah menerima transfer dana refund. Laporan masalah selesai."
            );

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Konfirmasi berhasil. Laporan masalah selesai.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("BuyerConfirmRefund Error #{$id}: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ============================================================
    // PRIVATE: Proses refund ke pembeli
    // ============================================================
    private function processRefundToBuyer(Dispute $dispute)
    {
        DB::transaction(function () use ($dispute) {
            $locked = Dispute::lockForUpdate()->find($dispute->id);
            if (!$locked || $locked->status === 'refunded') {
                return;
            }

            $transaction  = $locked->transaction;
            $amount       = $transaction->total_amount;
            $escrowAmount = $transaction->seller_amount;

            // Release escrow dari SellerBalance
            $sellerBalance = SellerBalance::where('user_id', $transaction->seller_id)->first();
            if ($sellerBalance) {
                $sellerBalance->pending_balance = max(0, $sellerBalance->pending_balance - $escrowAmount);
                $sellerBalance->save();
            }

            // Buat RefundRecord — pencatatan detail
            RefundRecord::createPending(
                transactionId: $transaction->id,
                buyerId:       $locked->buyer_id,
                amount:        $amount,
                disputeId:     $locked->id,
                notes:         "Refund Laporan #D{$locked->id} \u2014 Pesanan #{$transaction->id}",
            );

            $locked->update(['status' => 'refunded']);
            $transaction->update(['status' => 'disputed_refunded', 'buyer_can_rate' => false]);

            // Restore stock
            foreach ($transaction->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock', $item->quantity);
                }
            }

            $locked->addLog('system', null, 'refunded',
                "Dana Rp " . number_format($amount, 0, ',', '.') . " dicatat untuk refund ke rekening pembeli"
            );
        });
    }

    // ============================================================
    // PRIVATE: Lepas dana ke penjual dengan potongan platform
    // ============================================================
    private function releaseFundsToSeller(Dispute $dispute)
    {
        DB::transaction(function () use ($dispute) {
            $locked = Dispute::lockForUpdate()->find($dispute->id);
            if (!$locked || $locked->status === 'closed') {
                return;
            }

            $transaction = $locked->transaction;
            $grossAmount = $transaction->seller_amount;

            $feePercent  = (float) optional(
                \App\Models\SystemSetting::where('key', 'service_fee_percent')->first()
            )->value ?? 10;

            $platformFee = round($grossAmount * $feePercent / 100);
            $netToSeller = $grossAmount - $platformFee;

            // Release escrow via SellerBalance
            $sellerBalance = SellerBalance::getOrCreate($locked->seller_id);
            $sellerBalance->pending_balance = max(0, $sellerBalance->pending_balance - $grossAmount);
            $sellerBalance->available_balance += $netToSeller;
            $sellerBalance->total_earnings += $netToSeller;
            $sellerBalance->save();

            // Record platform fee
            \App\Models\PlatformEarning::recordEarning(
                $transaction->id,
                $platformFee,
                0,
                "{$feePercent}% service fee dari Laporan #D{$locked->id}"
            );

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
        $msg = "Pembeli membuka laporan untuk pesanan #{$transaction->id}. Alasan: {$dispute->reason}. Admin akan segera meninjau.";

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
