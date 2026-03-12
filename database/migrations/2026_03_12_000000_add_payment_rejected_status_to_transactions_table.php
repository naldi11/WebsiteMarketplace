<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Adding 'payment_rejected' to the status enum
        DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM('waiting_payment', 'pending', 'paid_verified', 'shipped', 'received', 'completed', 'cancelled', 'payment_rejected') DEFAULT 'waiting_payment'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Removing 'payment_rejected' - be careful as this might fail if there are existing rows with this status
        DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM('waiting_payment', 'pending', 'paid_verified', 'shipped', 'received', 'completed', 'cancelled') DEFAULT 'waiting_payment'");
    }
};
