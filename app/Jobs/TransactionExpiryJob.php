<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Models\TransactionStatusLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionExpiryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Running TransactionExpiryJob...');

        // Find transactions waiting for payment that have expired
        $expiredTransactions = Transaction::where('status', 'waiting_payment')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->with('items.product')
            ->get();

        if ($expiredTransactions->isEmpty()) {
            return;
        }

        foreach ($expiredTransactions as $transaction) {
            try {
                DB::transaction(function () use ($transaction) {
                    // 1. Restore stock
                    foreach ($transaction->items as $item) {
                        if ($item->product) {
                            $item->product->increment('stock', $item->quantity);
                        }
                    }

                    // 2. Update status to cancelled
                    $transaction->update(['status' => 'cancelled']);

                    // 3. Log the status change
                    TransactionStatusLog::create([
                        'transaction_id' => $transaction->id,
                        'status' => 'cancelled',
                        'note' => 'Dibatalkan otomatis oleh sistem (melewati batas waktu pembayaran 24 jam)',
                        'changed_by' => null // System
                    ]);

                    Log::info("Transaction {$transaction->id} automatically cancelled due to payment expiry.");
                });
            } catch (\Exception $e) {
                Log::error("Failed to cancel expired transaction {$transaction->id}: " . $e->getMessage());
            }
        }
    }
}
