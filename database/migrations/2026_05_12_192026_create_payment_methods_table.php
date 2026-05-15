<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique(); // e.g. 'meypay_wallet', 'transfer_bca'
            $table->string('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('type')->default('manual'); // manual, wallet, gateway
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed data default
        DB::table('payment_methods')->insert([
            [
                'name'        => 'MeyPay Wallet',
                'code'        => 'meypay_wallet',
                'description' => 'Bayar langsung menggunakan saldo MeyPay Anda',
                'icon'        => null,
                'type'        => 'wallet',
                'is_active'   => true,
                'sort_order'  => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
