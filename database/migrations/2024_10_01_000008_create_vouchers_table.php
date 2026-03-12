<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->decimal('discount_amount', 12, 0); // Fixed amount reduction
            $table->integer('usage_limit')->default(100);
            $table->integer('usage_count')->default(0);
            $table->decimal('min_purchase', 12, 0)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->string('voucher_code')->nullable()->after('payment_proof');
            $table->decimal('discount_total', 12, 0)->default(0)->after('total_amount');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['voucher_code', 'discount_total']);
        });
    }
};
