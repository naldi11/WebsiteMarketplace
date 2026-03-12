<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Delivery options
            $table->enum('delivery_type', ['pickup', 'courier'])->default('courier')->after('status');
            $table->decimal('shipping_cost', 12, 2)->default(0)->after('delivery_type');
            $table->decimal('shipping_discount', 12, 2)->default(0)->after('shipping_cost');

            // Payment expiry (24 hours from checkout)
            $table->timestamp('expires_at')->nullable()->after('shipping_discount');

            // Buyer receipt confirmation timestamp
            $table->timestamp('receipt_confirmed_at')->nullable()->after('expires_at');

            // Seller's latest status note
            $table->string('seller_notes')->nullable()->after('receipt_confirmed_at');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_type',
                'shipping_cost',
                'shipping_discount',
                'expires_at',
                'receipt_confirmed_at',
                'seller_notes',
            ]);
        });
    }
};
