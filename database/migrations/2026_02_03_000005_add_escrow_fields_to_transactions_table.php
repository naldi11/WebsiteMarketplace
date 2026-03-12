<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('service_fee', 15, 2)->default(0)->after('admin_fee');
            $table->decimal('seller_amount', 15, 2)->default(0)->after('service_fee');
            $table->string('payment_method_code')->nullable()->after('payment_method');
            $table->foreignId('shipping_address_id')->nullable()->after('shipping_address');
            $table->string('midtrans_transaction_id')->nullable();
            $table->string('midtrans_status')->nullable();
            $table->timestamp('funds_released_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'service_fee',
                'seller_amount',
                'payment_method_code',
                'shipping_address_id',
                'midtrans_transaction_id',
                'midtrans_status',
                'funds_released_at'
            ]);
        });
    }
};
