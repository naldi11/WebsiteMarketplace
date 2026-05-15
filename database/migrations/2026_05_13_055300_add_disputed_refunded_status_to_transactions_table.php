<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'disputed' and 'refunded' to the status enum
        DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM(
            'waiting_payment',
            'pending',
            'paid_verified',
            'processing',
            'packed',
            'ready_to_ship',
            'shipped',
            'received',
            'completed',
            'cancelled',
            'payment_rejected',
            'disputed',
            'refunded'
        ) DEFAULT 'waiting_payment'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM(
            'waiting_payment',
            'pending',
            'paid_verified',
            'processing',
            'packed',
            'ready_to_ship',
            'shipped',
            'received',
            'completed',
            'cancelled',
            'payment_rejected'
        ) DEFAULT 'waiting_payment'");
    }
};
