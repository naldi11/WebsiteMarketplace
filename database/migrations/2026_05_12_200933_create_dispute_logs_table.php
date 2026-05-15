<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dispute_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispute_id')->constrained()->onDelete('cascade');
            $table->foreignId('transaction_id')->constrained()->onDelete('cascade');

            // Siapa yang melakukan aksi
            $table->enum('actor', ['system', 'buyer', 'seller', 'admin']);
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();

            // Aksi yang dilakukan
            $table->string('action'); // e.g. dispute_opened, admin_reviewed, buyer_won, refund_processed, etc.
            $table->text('notes')->nullable(); // Detail tambahan / pesan dari actor
            $table->json('metadata')->nullable(); // Data tambahan seperti nominal refund, resi, dll

            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispute_logs');
    }
};
