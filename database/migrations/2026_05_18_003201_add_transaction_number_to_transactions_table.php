<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'transaction_number')) {
                $table->string('transaction_number')->nullable()->after('id');
            }
            if (!Schema::hasColumn('transactions', 'meypay_va')) {
                $table->string('meypay_va')->nullable();
            }
            if (!Schema::hasColumn('transactions', 'meypay_va_token')) {
                $table->string('meypay_va_token')->nullable();
            }
            if (!Schema::hasColumn('transactions', 'meypay_qr_content')) {
                $table->string('meypay_qr_content')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $cols = [];
            foreach (['transaction_number','meypay_va','meypay_va_token','meypay_qr_content'] as $col) {
                if (Schema::hasColumn('transactions', $col)) $cols[] = $col;
            }
            if ($cols) $table->dropColumn($cols);
        });
    }
};
