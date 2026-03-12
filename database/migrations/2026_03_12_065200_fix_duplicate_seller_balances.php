<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\SellerBalance;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Recalculate all seller balances
        $balances = SellerBalance::all();

        foreach ($balances as $balance) {
            $userId = $balance->user_id;

            // Sum actual earnings from completed transactions
            $actualEarnings = Transaction::where('seller_id', $userId)
                ->where('status', 'completed')
                ->sum('seller_amount');

            // Recalculate available balance (Earnings - Withdrawn)
            $availableBalance = max(0, $actualEarnings - $balance->total_withdrawn);

            // Update the record
            $balance->update([
                'available_balance' => $availableBalance,
                'total_earnings' => $actualEarnings,
                // Optional: Recalculate pending balance if needed
                'pending_balance' => Transaction::where('seller_id', $userId)
                    ->whereIn('status', ['paid_verified', 'processing', 'packed', 'ready_to_ship', 'shipped', 'received'])
                    ->sum('seller_amount')
            ]);

            \Log::info("Recalculated balance for Seller #$userId: Available: $availableBalance, Earnings: $actualEarnings");
        }
    }

    public function down(): void
    {
        // No down migration needed for data fix
    }
};
