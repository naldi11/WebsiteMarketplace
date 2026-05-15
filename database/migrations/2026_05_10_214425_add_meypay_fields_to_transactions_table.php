<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('meypay_va')->nullable()->after('payment_method_code');
            $table->text('meypay_qr_content')->nullable()->after('meypay_va');
            $table->timestamp('paid_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['meypay_va', 'meypay_qr_content', 'paid_at']);
        });
    }
};
