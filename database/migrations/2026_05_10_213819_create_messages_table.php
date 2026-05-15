<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $header) {
            $header->id();
            $header->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $header->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
            $header->text('message');
            $header->boolean('is_read')->default(false);
            $header->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
