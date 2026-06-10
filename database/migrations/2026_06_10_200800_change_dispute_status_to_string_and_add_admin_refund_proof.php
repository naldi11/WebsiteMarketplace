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
        Schema::table('disputes', function (Blueprint $table) {
            $table->string('status')->default('open')->change();
            $table->string('admin_refund_proof')->nullable()->after('return_shipping_proof');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('disputes', function (Blueprint $table) {
            $table->dropColumn('admin_refund_proof');
            // Kita biarkan status tetap string saat rollback agar tidak memicu error enum jika ada data status baru.
        });
    }
};
