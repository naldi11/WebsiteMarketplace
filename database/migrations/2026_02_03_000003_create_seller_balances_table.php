<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('seller_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('available_balance', 15, 2)->default(0); // Saldo siap tarik
            $table->decimal('pending_balance', 15, 2)->default(0);   // Dana dalam escrow
            $table->decimal('total_withdrawn', 15, 2)->default(0);   // Total sudah ditarik
            $table->decimal('total_earnings', 15, 2)->default(0);    // Total pendapatan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_balances');
    }
};
