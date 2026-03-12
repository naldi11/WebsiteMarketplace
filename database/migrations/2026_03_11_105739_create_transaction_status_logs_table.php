<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transaction_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->onDelete('cascade');
            $table->string('status');
            $table->string('note')->nullable();
            $table->unsignedBigInteger('changed_by')->nullable(); // user_id who changed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_status_logs');
    }
};
