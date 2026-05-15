<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $header) {
            $header->id();
            $header->foreignId('wallet_id')->constrained()->onDelete('cascade');
            $header->decimal('amount', 15, 2); // Positive for credit, negative for debit
            $header->string('type'); // topup, payment, payout, refund, service_fee
            $header->string('reference_type')->nullable(); // transaction, dispute, etc.
            $header->unsignedBigInteger('reference_id')->nullable();
            $header->string('description');
            $header->decimal('balance_after', 15, 2); // Running balance for audit
            $header->string('status')->default('success'); // success, pending, failed
            $header->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
