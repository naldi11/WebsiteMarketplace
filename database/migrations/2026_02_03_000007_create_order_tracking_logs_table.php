<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_tracking_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->onDelete('cascade');
            $table->string('status'); // Status code
            $table->string('title'); // Display title
            $table->text('description')->nullable(); // Additional info
            $table->string('actor_type')->default('system'); // system, seller, buyer, admin
            $table->foreignId('actor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['transaction_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_tracking_logs');
    }
};
