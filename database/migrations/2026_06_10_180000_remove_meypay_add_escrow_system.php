<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop MeyPay columns from transactions
        Schema::table('transactions', function (Blueprint $table) {
            $columns = ['meypay_va', 'meypay_qr_content', 'meypay_va_token'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('transactions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        // 2. Drop wallet tables (child first, then parent)
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('wallets');

        // 3. Create refund_records table
        Schema::create('refund_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->onDelete('cascade');
            $table->foreignId('dispute_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->string('refund_method')->default('bank_transfer');
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_holder_name')->nullable();
            $table->string('transfer_proof')->nullable();
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->string('status')->default('pending'); // pending, completed, failed
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();

            $table->index(['status']);
            $table->index(['buyer_id']);
        });

        // 4. Remove meypay_wallet from payment_methods & seed 4 banks
        DB::table('payment_methods')->where('code', 'meypay_wallet')->delete();

        $banks = [
            ['name' => 'Bank BRI',     'code' => 'bank_bri',     'account_number' => '-', 'sort_order' => 1],
            ['name' => 'Bank BCA',     'code' => 'bank_bca',     'account_number' => '-', 'sort_order' => 2],
            ['name' => 'Bank Mandiri', 'code' => 'bank_mandiri', 'account_number' => '-', 'sort_order' => 3],
            ['name' => 'Bank BNI',     'code' => 'bank_bni',     'account_number' => '-', 'sort_order' => 4],
        ];

        foreach ($banks as $bank) {
            DB::table('payment_methods')->updateOrInsert(
                ['code' => $bank['code']],
                array_merge($bank, [
                    'description' => 'Transfer manual ke rekening ' . $bank['name'],
                    'type'        => 'bank_transfer',
                    'is_active'   => true,
                    'admin_fee'   => 0,
                    'admin_fee_percent' => 0,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ])
            );
        }

        // 5. Update legacy transactions that used meypay_wallet
        DB::table('transactions')
            ->where('payment_method_code', 'meypay_wallet')
            ->update(['payment_method_code' => 'legacy_wallet', 'payment_method' => 'Legacy Wallet']);
    }

    public function down(): void
    {
        Schema::dropIfExists('refund_records');

        // Restore meypay columns
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'meypay_va')) {
                $table->string('meypay_va')->nullable();
            }
            if (!Schema::hasColumn('transactions', 'meypay_qr_content')) {
                $table->text('meypay_qr_content')->nullable();
            }
            if (!Schema::hasColumn('transactions', 'meypay_va_token')) {
                $table->string('meypay_va_token')->nullable();
            }
        });

        // Restore wallet tables
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('wallet_number')->unique();
            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('pending_balance', 15, 2)->default(0);
            $table->string('pin')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->string('type');
            $table->string('description')->nullable();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('balance_after', 15, 2);
            $table->string('status')->default('success');
            $table->timestamps();
        });
    }
};
