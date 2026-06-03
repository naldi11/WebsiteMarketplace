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
        if (!Schema::hasTable('payment_methods')) {
            return; // Tabel dibuat oleh migration 2026_05_12 yang sudah include kolom ini
        }
        if (!Schema::hasColumn('payment_methods', 'account_number')) {
            Schema::table('payment_methods', function (Blueprint $table) {
                $table->string('account_number')->nullable()->after('name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('payment_methods') && Schema::hasColumn('payment_methods', 'account_number')) {
            Schema::table('payment_methods', function (Blueprint $table) {
                $table->dropColumn('account_number');
            });
        }
    }
};
