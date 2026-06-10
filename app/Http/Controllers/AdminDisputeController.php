<?php

namespace App\Http\Controllers;

use App\Models\Dispute;
use App\Models\Message;
use App\Models\Transaction;
use App\Models\SellerBalance;
use App\Models\RefundRecord;
use App\Models\PlatformEarning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminDisputeController extends Controller
{
    private function checkAdmin()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Akses Ditolak.');
        }
    }

    // ─────────────────────────────────────────────────────────────
    // INDEX — Daftar semua dispute
    // ─────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $this->checkAdmin();

        $status = $request->query('status', 'open');

        $query = Dispute::with(['transaction', 'buyer', 'seller', 'resolvedBy'])
            ->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $disputes = $query->paginate(15)->withQueryString();

        $counts = [
            'open'       => Dispute::whereIn('status', ['open', 'admin_reviewing'])->count(),
            'buyer_won'  => Dispute::whereIn('status', ['buyer_won', 'buyer_shipping_back', 'seller_received_back'])->count(),
            'seller_won' => Dispute::where('status', 'seller_won')->count(),
            'refunded'   => Dispute::where('status', 'refunded')->count(),
            'closed'     => Dispute::where('status', 'closed')->count(),
            'all'        => Dispute::count(),
        ];

        return view('admin.disputes.index', compact('disputes', 'counts', 'status'));
    }

    // ─────────────────────────────────────────────────────────────
    // SHOW — Detail dispute
    // ─────────────────────────────────────────────────────────────
    public function show($id)
    {
        $this->checkAdmin();

        $dispute = Dispute::with([
            'transaction.items.product',
            'transaction.buyer',
            'transaction.seller',
            'buyer',
            'seller',
            'resolvedBy',
            'logs' => fn($q) => $q->orderBy('created_at', 'asc'),
        ])->findOrFail($id);

        $messages = $this->getDisputeMessages($dispute);

        return view('admin.disputes.show', compact('dispute', 'messages'));
    }

    // ─────────────────────────────────────────────────────────────
    // RESOLVE — Admin putuskan pemenang
    // ─────────────────────────────────────────────────────────────
    public function resolve(Request $request, $id)
    {
        $this->checkAdmin();
        $admin = auth()->user();

        $request->validate([
            'winner'      => 'required|in:buyer,seller',
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        $dispute = Dispute::with('transaction')->findOrFail($id);

        if (!in_array($dispute->status, ['open', 'admin_reviewing'])) {
            return back()->with('error', 'Dispute sudah diproses. Status: ' . $dispute->status);
        }

        DB::beginTransaction();
        try {
            $winner = $request->winner;
            $notes  = $request->admin_notes ?? '-';

            $dispute->update([
                'status'            => $winner === 'buyer' ? 'buyer_won' : 'seller_won',
                'winner'            => $winner,
                'resolved_by'       => $admin->id,
                'admin_notes'       => $notes,
                'admin_reviewed_at' => now(),
            ]);

            $dispute->addLog('admin', $admin->id, 'admin_resolved',
                "Admin memutuskan pemenang: {$winner}. Catatan: {$notes}",
                ['winner' => $winner]
            );

            if ($winner === 'seller') {
                // ── PENJUAL MENANG ────────────────────────────────
                $this->releaseFundsToSeller($dispute->transaction, $dispute, $admin->id);

                // Tandai transaksi: pembeli tidak boleh beri rating
                $dispute->transaction->update([
                    'status'        => 'completed',
                    'buyer_can_rate' => 0,
                ]);

                $dispute->update(['status' => 'seller_won', 'resolved_at' => now()]);

                $dispute->addLog('system', null, 'dispute_closed_seller_won',
                    'Dana dilepas ke penjual (dipotong 10% platform fee). Pembeli tidak dapat memberi rating.');

                $this->sendAdminSystemMessage($dispute,
                    "Laporan diselesaikan. Penjual dinyatakan menang.\nCatatan admin: {$notes}"
                );
            } else {
                // ── PEMBELI MENANG ────────────────────────────────
                $this->sendAdminSystemMessage($dispute,
                    "Laporan diselesaikan. Pembeli dinyatakan menang.\nCatatan admin: {$notes}\nSilakan kirim kembali barang ke penjual dan input nomor resi di aplikasi."
                );
            }

            DB::commit();
            $winnerText = $winner === 'buyer' ? 'Pembeli' : 'Penjual';
            return back()->with('success', "✅ Keputusan berhasil disimpan. {$winnerText} dinyatakan menang.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("AdminDisputeResolve #{$id}: " . $e->getMessage());
            return back()->with('error', 'Gagal memproses: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────
    // ADMIN CONFIRM RECEIVED — Admin paksa konfirmasi penjual terima barang → trigger refund
    // ─────────────────────────────────────────────────────────────
    public function adminConfirmReceived(Request $request, $id)
    {
        $this->checkAdmin();
        $admin = auth()->user();

        $dispute = Dispute::with('transaction')->findOrFail($id);

        if (!in_array($dispute->status, ['buyer_won', 'buyer_shipping_back'])) {
            return back()->with('error', 'Tidak bisa konfirmasi pada status: ' . $dispute->status);
        }

        DB::beginTransaction();
        try {
            $dispute->update([
                'status'                  => 'seller_received_back',
                'seller_received_back_at' => now(),
            ]);

            $dispute->addLog('admin', $admin->id, 'admin_confirm_received',
                'Admin memaksa konfirmasi penjual telah menerima kembali barang');

            // Inisiasi proses refund manual
            $this->initiateRefundProcess($dispute, $admin->id);

            DB::commit();
            return back()->with('success', '✅ Penerimaan barang berhasil dikonfirmasi. Silakan proses refund manual ke rekening pembeli!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("AdminConfirmReceived #{$id}: " . $e->getMessage());
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────
    // FORCE REFUND — Admin refund langsung (bypass pengembalian barang)
    // ─────────────────────────────────────────────────────────────
    public function forceRefund(Request $request, $id)
    {
        $this->checkAdmin();
        $admin = auth()->user();

        $request->validate(['admin_notes' => 'nullable|string|max:1000']);

        $dispute = Dispute::with('transaction')->findOrFail($id);

        if (!in_array($dispute->status, ['buyer_won', 'buyer_shipping_back', 'seller_received_back', 'open', 'admin_reviewing'])) {
            return back()->with('error', 'Tidak bisa force refund pada status: ' . $dispute->status);
        }

        DB::beginTransaction();
        try {
            // Inisiasi proses refund manual (langsung bypass pengembalian barang)
            $this->initiateRefundProcess($dispute, $admin->id);

            $dispute->addLog('admin', $admin->id, 'admin_force_refund',
                'Admin memproses refund paksa ke pembeli. Catatan: ' . ($request->admin_notes ?? '-')
            );

            DB::commit();
            return back()->with('success', '✅ Refund berhasil dicatat! Silakan lakukan transfer manual dan unggah buktinya.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("AdminForceRefund #{$id}: " . $e->getMessage());
            return back()->with('error', 'Gagal refund: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────
    // REFUND MANUAL — Admin mengunggah bukti transfer manual
    // ─────────────────────────────────────────────────────────────
    public function refundManual(Request $request, $id)
    {
        $this->checkAdmin();
        $admin = auth()->user();

        $request->validate([
            'transfer_proof'      => 'required|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'bank_name'           => 'required|string|max:100',
            'account_number'      => 'required|string|max:50',
            'account_holder_name' => 'required|string|max:100',
            'notes'               => 'nullable|string|max:500',
        ]);

        $dispute = Dispute::with('transaction')->findOrFail($id);

        if ($dispute->status !== 'seller_received_back') {
            return back()->with('error', 'Tidak bisa memproses refund manual pada status: ' . $dispute->status);
        }

        DB::beginTransaction();
        try {
            $proofPath = $request->file('transfer_proof')->store('refund_proofs', 'public');

            // Cari RefundRecord terkait dispute ini
            $refundRecord = RefundRecord::where('dispute_id', $dispute->id)
                ->where('status', 'pending')
                ->first();

            if ($refundRecord) {
                $refundRecord->update([
                    'bank_name'           => $request->bank_name,
                    'account_number'      => $request->account_number,
                    'account_holder_name' => $request->account_holder_name,
                    'transfer_proof'      => $proofPath,
                    'admin_id'            => $admin->id,
                    'notes'               => $request->notes ?? $refundRecord->notes,
                    'status'              => 'completed',
                    'refunded_at'         => now(),
                ]);
            } else {
                // Buat jika tidak ada
                RefundRecord::create([
                    'transaction_id'      => $dispute->transaction_id,
                    'dispute_id'          => $dispute->id,
                    'buyer_id'            => $dispute->buyer_id,
                    'amount'              => $dispute->transaction->total_amount,
                    'refund_method'       => 'bank_transfer',
                    'bank_name'           => $request->bank_name,
                    'account_number'      => $request->account_number,
                    'account_holder_name' => $request->account_holder_name,
                    'transfer_proof'      => $proofPath,
                    'admin_id'            => $admin->id,
                    'notes'               => $request->notes ?? "Refund dari Dispute #D{$dispute->id}",
                    'status'              => 'completed',
                    'refunded_at'         => now(),
                ]);
            }

            // Update status dispute ke menunggu konfirmasi pembeli
            $dispute->update([
                'status'              => 'refund_transferred',
                'admin_refund_proof'  => $proofPath,
            ]);

            $dispute->addLog('admin', $admin->id, 'admin_refund_transferred',
                "Admin telah mentransfer dana ke rekening pembeli manual. Bank: {$request->bank_name}, No Rek: {$request->account_number}. Menunggu konfirmasi pembeli."
            );

            $this->sendAdminSystemMessage($dispute,
                "[Admin] Bukti transfer dana refund sebesar Rp " . number_format($dispute->transaction->total_amount, 0, ',', '.') . " telah diunggah. Pembeli harap konfirmasi penerimaan dana."
            );

            DB::commit();
            return back()->with('success', '✅ Bukti transfer berhasil diunggah. Menunggu konfirmasi pembeli.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("AdminRefundManual #{$id}: " . $e->getMessage());
            return back()->with('error', 'Gagal memproses refund manual: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────
    // MARK REVIEWING
    // ─────────────────────────────────────────────────────────────
    public function markReviewing($id)
    {
        $this->checkAdmin();
        $admin = auth()->user();

        $dispute = Dispute::with('transaction')->findOrFail($id);

        if ($dispute->status !== 'open') {
            return back()->with('error', 'Hanya dispute berstatus open yang bisa ditinjau.');
        }

        $dispute->update(['status' => 'admin_reviewing']);
        $dispute->addLog('admin', $admin->id, 'admin_reviewing', 'Admin mulai meninjau kasus ini');

        $this->sendAdminSystemMessage($dispute,
            "Laporan sedang ditinjau oleh admin. Harap menunggu keputusan."
        );

        return back()->with('success', 'Status dispute diperbarui menjadi: Sedang Ditinjau');
    }

    // ─────────────────────────────────────────────────────────────
    // GOD VIEW CHAT — Admin pantau percakapan buyer-seller
    // ─────────────────────────────────────────────────────────────
    public function viewChat($id)
    {
        $this->checkAdmin();

        $dispute = Dispute::with(['buyer', 'seller', 'transaction.buyer', 'transaction.seller'])->findOrFail($id);

        $messages = $this->getDisputeMessages($dispute);

        return view('admin.disputes.chat', compact('dispute', 'messages'));
    }

    // ─────────────────────────────────────────────────────────────
    // SEND ADMIN CHAT — Admin kirim pesan ke chat buyer-seller
    // ─────────────────────────────────────────────────────────────
    public function sendAdminChat(Request $request, $id)
    {
        $admin = auth()->user();
        $request->validate(['message' => 'required|string|max:2000']);
        $dispute = Dispute::with('transaction')->findOrFail($id);

        // Kirim dengan sender=seller agar masuk ke room chat buyer-seller yang ada,
        // bukan membuat conversation baru dengan "Super Admin" di mobile.
        // Prefix [Admin] agar pembeli & penjual tahu ini pesan intervensi admin.
        $text = '[Admin] ' . $request->message;

        Message::create([
            'sender_id'   => $dispute->seller_id,
            'receiver_id' => $dispute->buyer_id,
            'message'     => $text,
            'is_read'     => 0,
        ]);

        $dispute->addLog('admin', $admin->id, 'admin_sent_chat',
            'Admin mengirim pesan: ' . substr($request->message, 0, 100)
        );

        return back()->with('success', 'Pesan berhasil dikirim.');
    }

    // ─────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────

    private function releaseFundsToSeller(Transaction $transaction, Dispute $dispute, ?int $adminId = null)
    {
        $grossAmount  = $transaction->seller_amount ?? $transaction->total_amount;
        $platformFee  = round($grossAmount * 0.10);
        $netToSeller  = $grossAmount - $platformFee;

        // Release escrow via SellerBalance
        $sellerBalance = SellerBalance::getOrCreate($transaction->seller_id);
        $sellerBalance->pending_balance = max(0, $sellerBalance->pending_balance - $grossAmount);
        $sellerBalance->available_balance += $netToSeller;
        $sellerBalance->total_earnings += $netToSeller;
        $sellerBalance->save();

        PlatformEarning::recordEarning(
            $transaction->id,
            $platformFee,
            0,
            "10% service fee dispute #{$dispute->id} (TXN #{$transaction->id})"
        );

        $transaction->update([
            'status'            => 'completed',
            'funds_released_at' => now(),
        ]);

        $dispute->addLog(
            $adminId ? 'admin' : 'system',
            $adminId,
            'funds_released_to_seller',
            "Dana Rp " . number_format($netToSeller, 0, ',', '.') . " dilepas ke penjual " .
            "(gross: Rp " . number_format($grossAmount, 0, ',', '.') . ", " .
            "fee platform 10%: Rp " . number_format($platformFee, 0, ',', '.') . ")"
        );
    }

    private function initiateRefundProcess(Dispute $dispute, ?int $adminId = null)
    {
        $transaction = $dispute->transaction;
        $amount      = $transaction->total_amount;
        $escrowAmount = $transaction->seller_amount;

        // Release escrow dari SellerBalance
        $sellerBalance = SellerBalance::where('user_id', $transaction->seller_id)->first();
        if ($sellerBalance) {
            $sellerBalance->pending_balance = max(0, $sellerBalance->pending_balance - $escrowAmount);
            $sellerBalance->save();
        }

        // Cek jika sudah ada RefundRecord pending agar tidak duplikat
        $exists = RefundRecord::where('dispute_id', $dispute->id)->first();
        if (!$exists) {
            RefundRecord::createPending(
                transactionId: $transaction->id,
                buyerId:       $dispute->buyer_id,
                amount:        $amount,
                disputeId:     $dispute->id,
                adminId:       $adminId,
                notes:         "Refund dari Dispute #D{$dispute->id}",
            );
        }

        // Update status dispute ke seller_received_back jika belum
        if ($dispute->status !== 'seller_received_back') {
            $dispute->update([
                'status'                  => 'seller_received_back',
                'seller_received_back_at' => now(),
            ]);
        }

        // Restore stock
        foreach ($transaction->items as $item) {
            if ($item->product) {
                $item->product->increment('stock', $item->quantity);
            }
        }

        $dispute->addLog(
            $adminId ? 'admin' : 'system',
            $adminId,
            'refund_initiated',
            "Pengembalian barang dikonfirmasi. Dana Rp " . number_format($amount, 0, ',', '.') . " dicatat untuk refund. Menunggu admin melakukan transfer manual.",
            ['amount' => $amount]
        );

        $this->sendAdminSystemMessage($dispute,
            "Refund sebesar Rp " . number_format($amount, 0, ',', '.') . " siap ditransfer secara manual oleh Admin. Harap tunggu bukti transfer."
        );
    }

    /**
     * Query pesan percakapan untuk sebuah dispute, tanpa duplikat.
     * Menampilkan: buyer↔seller + admin→buyer saja (bukan admin→seller).
     */
    private function getDisputeMessages(Dispute $dispute)
    {
        $raw = Message::where(function ($q) use ($dispute) {
                // buyer → seller
                $q->where(function ($inner) use ($dispute) {
                    $inner->where('sender_id', $dispute->buyer_id)
                          ->where('receiver_id', $dispute->seller_id);
                })
                // seller → buyer
                ->orWhere(function ($inner) use ($dispute) {
                    $inner->where('sender_id', $dispute->seller_id)
                          ->where('receiver_id', $dispute->buyer_id);
                })
                // admin → buyer saja (bukan admin → seller)
                ->orWhere(function ($inner) use ($dispute) {
                    $inner->whereNotIn('sender_id', [$dispute->buyer_id, $dispute->seller_id])
                          ->where('receiver_id', $dispute->buyer_id);
                });
            })
            ->with(['sender'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Dedup: hilangkan pesan dengan konten+waktu identik (sisa data lama duplikat di DB)
        $seen = [];
        return $raw->filter(function ($msg) use (&$seen) {
            $key = $msg->sender_id . '|' . $msg->created_at->format('Y-m-d H:i:s') . '|' . substr($msg->message, 0, 100);
            if (isset($seen[$key])) return false;
            $seen[$key] = true;
            return true;
        })->values();
    }

    /**
     * Kirim pesan sistem ke buyer saja (seller mendapat info melalui halaman dispute).
     * Satu pesan = tidak ada duplikat di tampilan admin.
     */
    private function sendAdminSystemMessage(Dispute $dispute, string $message)
    {
        try {
            $transaction = $dispute->transaction;
            // Hanya satu pesan: sistem → buyer
            Message::create([
                'sender_id'   => $transaction->seller_id,
                'receiver_id' => $transaction->buyer_id,
                'message'     => $message,
                'is_read'     => 0,
            ]);
        } catch (\Exception $e) {
            Log::warning("AdminDispute system message failed: " . $e->getMessage());
        }
    }
}
