<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // 1 = boleh beri rating (default), 0 = tidak boleh (jika penjual menang dispute)
            $table->tinyInteger('buyer_can_rate')->default(1)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('buyer_can_rate');
        });
    }
};
