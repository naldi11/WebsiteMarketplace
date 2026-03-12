<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SellerBalance;
use App\Models\Transaction;
use App\Models\User;

class RecalculateSellerBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'balance:recalculate {--seller_id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate seller balances from transactions (fix negative balance bug)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Recalculating Seller Balances...');
        $this->newLine();

        // Get sellers from transactions (users who have sold items)
        $sellerIds = Transaction::distinct('seller_id')->pluck('seller_id');

        if ($this->option('seller_id')) {
            $sellerIds = collect([$this->option('seller_id')]);
        }

        $sellers = User::whereIn('id', $sellerIds)->get();

        if ($sellers->isEmpty()) {
            $this->error('No sellers found!');
            return 1;
        }

        $fixedCount = 0;
        $bar = $this->output->createProgressBar($sellers->count());
        $bar->start();

        foreach ($sellers as $seller) {
            // Calculate pending balance: paid_verified, shipped, received (waiting for admin release)
            $pendingBalance = Transaction::where('seller_id', $seller->id)
                ->whereIn('status', ['paid_verified', 'shipped', 'received'])
                ->sum('seller_amount');

            // Calculate available balance: should be managed separately, but let's set to 0 for now until properly released
            // For now, we'll keep existing available_balance and only fix pending
            $sellerBalance = SellerBalance::firstOrCreate(
                ['user_id' => $seller->id],
                ['pending_balance' => 0, 'available_balance' => 0]
            );


            $oldPending = $sellerBalance->pending_balance ?? 0;
            $oldAvailable = $sellerBalance->available_balance ?? 0;

            // Only update pending balance (available is managed by release funds)
            $sellerBalance->update([
                'pending_balance' => $pendingBalance ?? 0,
            ]);

            if ($oldPending != $pendingBalance) {
                $fixedCount++;
                $this->newLine();
                $this->warn("📝 {$seller->name} (ID: {$seller->id}):");
                $this->line("   Pending:   Rp " . number_format($oldPending, 0, ',', '.') . " → Rp " . number_format($pendingBalance ?? 0, 0, ',', '.'));
                $this->line("   Available: Rp " . number_format($oldAvailable, 0, ',', '.') . " (unchanged)");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ Recalculation Complete!");
        $this->info("   Total Sellers: {$sellers->count()}");
        $this->info("   Balances Fixed: {$fixedCount}");

        return 0;
    }
}
