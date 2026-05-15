<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->onDelete('cascade');
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');

            // Laporan
            $table->string('reason'); // Barang tidak sesuai, Barang rusak, dll
            $table->text('description')->nullable();
            $table->json('evidence_photos')->nullable(); // bukti foto pembeli

            // Status dispute
            // open → admin_reviewing → buyer_won / seller_won
            // buyer_won → buyer_shipping_back → seller_received_back → refunded
            $table->enum('status', [
                'open',
                'admin_reviewing',
                'buyer_won',
                'seller_won',
                'buyer_shipping_back',
                'seller_received_back',
                'refunded',
                'closed',
            ])->default('open');

            $table->enum('winner', ['buyer', 'seller'])->nullable();

            // Informasi admin
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_notes')->nullable();

            // Timestamp tahapan
            $table->timestamp('admin_reviewed_at')->nullable();
            $table->timestamp('buyer_shipped_back_at')->nullable();    // Pembeli kirim barang balik
            $table->timestamp('seller_received_back_at')->nullable();  // Penjual terima balik
            $table->timestamp('refunded_at')->nullable();              // Refund selesai
            $table->timestamp('resolved_at')->nullable();

            // Resi pengiriman balik dari pembeli
            $table->string('return_tracking_number')->nullable();
            $table->string('return_courier')->nullable();

            // Chat room yg digunakan untuk dispute ini (user_id penjual sbg referensi)
            $table->foreignId('conversation_with_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disputes');
    }
};
