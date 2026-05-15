<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Waktu dispute dibuka (shipped_at sudah ada sebelumnya)
            if (!Schema::hasColumn('transactions', 'disputed_at')) {
                $table->timestamp('disputed_at')->nullable()->after('updated_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumnIfExists('shipped_at');
            $table->dropColumnIfExists('disputed_at');
        });
    }
};
