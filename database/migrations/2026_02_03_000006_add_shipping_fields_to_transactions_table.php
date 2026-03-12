<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('courier')->nullable()->after('shipping_address_id');
            $table->string('tracking_number')->nullable()->after('courier');
            $table->timestamp('shipped_at')->nullable()->after('tracking_number');
            $table->timestamp('received_at')->nullable()->after('shipped_at');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['courier', 'tracking_number', 'shipped_at', 'received_at']);
        });
    }
};
