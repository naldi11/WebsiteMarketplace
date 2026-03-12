<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('seller_balances', function (Blueprint $table) {
            $table->decimal('withdrawn_balance', 12, 2)->default(0)->after('pending_balance');
        });
    }

    public function down(): void
    {
        Schema::table('seller_balances', function (Blueprint $table) {
            $table->dropColumn('withdrawn_balance');
        });
    }
};
