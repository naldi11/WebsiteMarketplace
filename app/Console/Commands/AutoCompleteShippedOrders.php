<?php

namespace App\Console\Commands;

use App\Models\PlatformEarning;
use App\Models\Transaction;
use App\Models\TransactionStatusLog;
use App\Models\Wallet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoCompleteShippedOrders extends Command
{
    protected $signature   = 'orders:auto-complete';
    protected $description = 'Auto-complete shipped orders after 3 days with no buyer action';

    public function handle()
    {
        $cutoff = now()->subDays(3);

        // Cari transaksi yang:
        // 1. Status masih "shipped"
        // 2. shipped_at sudah > 3 hari
        // 3. Belum ada dispute
        $transactions = Transaction::where('status', 'shipped')
            ->where('shipped_at', '<=', $cutoff)
            ->whereDoesntHave('dispute', function ($q) {
                $q->whereNotIn('status', ['closed']);
            })
            ->get();

        $this->info("Found {$transactions->count()} orders to auto-complete.");

        foreach ($transactions as $tx) {
            try {
                $grossAmount = $tx->seller_amount;
                $platformFee = round($grossAmount * 0.10);
                $netToSeller = $grossAmount - $platformFee;

                $buyerWallet  = Wallet::getOrCreate($tx->buyer_id);
                $sellerWallet = Wallet::getOrCreate($tx->seller_id);

                // Kurangi pending_balance buyer
                if ($buyerWallet->pending_balance >= $grossAmount) {
                    $buyerWallet->pending_balance -= $grossAmount;
                    $buyerWallet->save();
                }

                // Kredit ke penjual (minus 10%)
                $sellerWallet->credit(
                    $netToSeller,
                    'payout',
                    "Auto-complete #TXN-{$tx->id} (3 hari tanpa konfirmasi, dipotong 10%)",
                    'transaction',
                    $tx->id
                );

                // Catat pendapatan platform
                PlatformEarning::recordEarning(
                    $tx->id,
                    $platformFee,
                    0,
                    "10% auto-complete fee dari TXN #{$tx->id}"
                );

                // Update status
                $tx->update([
                    'status'            => 'completed',
                    'funds_released_at' => now(),
                ]);

                TransactionStatusLog::create([
                    'transaction_id' => $tx->id,
                    'status'         => 'completed',
                    'note'           => "Auto-complete: pembeli tidak konfirmasi dalam 3 hari. Dana Rp " .
                                       number_format($netToSeller, 0, ',', '.') .
                                       " diteruskan ke penjual (10% fee = Rp " .
                                       number_format($platformFee, 0, ',', '.') . ")",
                    'changed_by'     => null,
                ]);

                $this->info("✅ TXN #{$tx->id} auto-completed. Net to seller: Rp " . number_format($netToSeller, 0, ',', '.'));
                Log::info("AutoComplete TXN #{$tx->id}: net={$netToSeller}, fee={$platformFee}");

            } catch (\Exception $e) {
                $this->error("❌ TXN #{$tx->id} failed: " . $e->getMessage());
                Log::error("AutoComplete Error TXN #{$tx->id}: " . $e->getMessage());
            }
        }

        $this->info('Done.');
        return 0;
    }
}
