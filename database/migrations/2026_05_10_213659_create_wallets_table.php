<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $header) {
            $header->id();
            $header->foreignId('user_id')->constrained()->onDelete('cascade');
            $header->string('wallet_number')->unique();
            $header->decimal('balance', 15, 2)->default(0);
            $header->decimal('pending_balance', 15, 2)->default(0); // For Escrow
            $header->string('pin')->nullable();
            $header->boolean('is_active')->default(true);
            $header->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
