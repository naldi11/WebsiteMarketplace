<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->enum('discount_type', ['fixed', 'percent'])->default('fixed')->after('code');
            $table->decimal('max_discount_amount', 12, 0)->nullable()->after('discount_amount');
            $table->foreignId('target_user_id')->nullable()->constrained('users')->nullOnDelete()->after('min_purchase');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropForeign(['target_user_id']);
            $table->dropColumn(['discount_type', 'max_discount_amount', 'target_user_id']);
        });
    }
};
