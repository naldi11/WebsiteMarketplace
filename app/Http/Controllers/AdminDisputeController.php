<?php

namespace App\Http\Controllers;

use App\Models\Dispute;
use App\Models\Message;
use App\Models\Transaction;
use App\Models\Wallet;
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

            $this->sendAdminSystemMessage($dispute,
                "Admin mengkonfirmasi penjual telah menerima barang. Refund sedang diproses."
            );

            // Auto trigger refund
            $this->processRefundToBuyer($dispute, $admin->id);

            DB::commit();
            return back()->with('success', '✅ Penerimaan barang dikonfirmasi. Refund otomatis telah diproses!');
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
            $this->processRefundToBuyer($dispute, $admin->id);

            $dispute->addLog('admin', $admin->id, 'admin_force_refund',
                'Admin memproses refund paksa ke pembeli. Catatan: ' . ($request->admin_notes ?? '-')
            );

            DB::commit();
            return back()->with('success', '✅ Refund berhasil diproses ke Wallet pembeli!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("AdminForceRefund #{$id}: " . $e->getMessage());
            return back()->with('error', 'Gagal refund: ' . $e->getMessage());
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

        $buyerWallet  = Wallet::getOrCreate($transaction->buyer_id);
        $sellerWallet = Wallet::getOrCreate($transaction->seller_id);

        if ($buyerWallet->pending_balance >= $grossAmount) {
            $buyerWallet->pending_balance -= $grossAmount;
            $buyerWallet->save();
        }

        $sellerWallet->credit(
            $netToSeller,
            'payout',
            "Penjualan #TXN-{$transaction->id} (Dispute #{$dispute->id}, potongan 10% platform)",
            'transaction',
            $transaction->id
        );

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

    private function processRefundToBuyer(Dispute $dispute, ?int $adminId = null)
    {
        $transaction = $dispute->transaction;
        $amount      = $transaction->total_amount;

        $buyerWallet = Wallet::getOrCreate($dispute->buyer_id);

        if ($buyerWallet->pending_balance >= $amount) {
            $buyerWallet->pending_balance -= $amount;
            $buyerWallet->save();
        }

        $buyerWallet->credit(
            $amount,
            'refund',
            "Refund Dispute #D{$dispute->id} — Pesanan #{$transaction->id}",
            'dispute',
            $dispute->id
        );

        $dispute->update([
            'status'      => 'refunded',
            'refunded_at' => now(),
            'resolved_at' => now(),
        ]);

        $transaction->update([
            'status'            => 'refunded',
            'funds_released_at' => now(),
        ]);

        $dispute->addLog(
            $adminId ? 'admin' : 'system',
            $adminId,
            'refund_processed',
            "Refund Rp " . number_format($amount, 0, ',', '.') . " berhasil dikreditkan ke MeyPay Wallet pembeli",
            ['amount' => $amount, 'buyer_balance_after' => $buyerWallet->balance]
        );

        $this->sendAdminSystemMessage($dispute,
            "Rp " . number_format($amount, 0, ',', '.') . " telah dikembalikan ke MeyPay Wallet pembeli."
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
