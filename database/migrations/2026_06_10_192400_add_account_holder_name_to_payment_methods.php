<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payment_methods') && !Schema::hasColumn('payment_methods', 'account_holder_name')) {
            Schema::table('payment_methods', function (Blueprint $table) {
                $table->string('account_holder_name')->nullable()->default('Administrasi Market');
            });

            // Set default account_holder_name for existing banks
            DB::table('payment_methods')
                ->whereIn('code', ['bank_bri', 'bank_bca', 'bank_mandiri', 'bank_bni'])
                ->update(['account_holder_name' => 'Administrasi Market']);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('payment_methods') && Schema::hasColumn('payment_methods', 'account_holder_name')) {
            Schema::table('payment_methods', function (Blueprint $table) {
                $table->dropColumn('account_holder_name');
            });
        }
    }
};
