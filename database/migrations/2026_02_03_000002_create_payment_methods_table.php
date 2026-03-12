<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // bank_bca, gopay, qris, etc.
            $table->string('name'); // BCA Virtual Account, GoPay, etc.
            $table->enum('type', ['bank_transfer', 'ewallet', 'qris', 'credit_card', 'cod']);
            $table->string('icon')->nullable();
            $table->text('instructions')->nullable(); // Cara pembayaran
            $table->decimal('admin_fee', 10, 2)->default(0); // Biaya admin per metode
            $table->decimal('admin_fee_percent', 5, 2)->default(0); // Fee persentase
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
