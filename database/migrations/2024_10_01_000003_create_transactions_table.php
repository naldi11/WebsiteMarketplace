<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade'); // Transaction per seller
            $table->text('shipping_address');
            $table->string('payment_method');
            $table->text('message')->nullable();
            $table->decimal('total_amount', 15, 2);
            $table->decimal('admin_fee', 15, 2)->default(0);
            $table->enum('status', ['waiting_payment', 'pending', 'paid_verified', 'shipped', 'received', 'completed', 'cancelled'])->default('waiting_payment');
            $table->string('payment_proof')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
