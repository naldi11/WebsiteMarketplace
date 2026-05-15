<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_logs', function (Blueprint $table) {
            $table->id();
            $table->string('device_unique_id')->unique();
            $table->foreignId('first_user_id')->constrained('users');
            $table->boolean('is_new_user_claimed')->default(false);
            $table->timestamps();
        });

        Schema::create('user_vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('voucher_id')->constrained()->onDelete('cascade');
            $table->boolean('is_used')->default(false);
            $table->timestamp('claimed_at')->useCurrent();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });

        Schema::table('vouchers', function (Blueprint $table) {
            $table->string('name')->after('code')->nullable();
            $table->integer('quota_total')->default(100)->after('usage_limit');
            $table->timestamp('start_date')->nullable()->after('quota_total');
            $table->timestamp('end_date')->nullable()->after('start_date');
            $table->text('description')->nullable()->after('terms');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_vouchers');
        Schema::dropIfExists('device_logs');
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn(['name', 'quota_total', 'start_date', 'end_date', 'description']);
        });
    }
};
