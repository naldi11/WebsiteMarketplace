<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM('waiting_payment', 'pending', 'paid_verified', 'processing', 'packed', 'ready_to_ship', 'shipped', 'received', 'completed', 'cancelled', 'payment_rejected') DEFAULT 'waiting_payment'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM('waiting_payment', 'pending', 'paid_verified', 'shipped', 'received', 'completed', 'cancelled', 'payment_rejected') DEFAULT 'waiting_payment'");
    }
};
