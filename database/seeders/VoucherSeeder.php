<?php

namespace Database\Seeders;

use App\Models\Voucher;
use Illuminate\Database\Seeder;

class VoucherSeeder extends Seeder
{
    public function run(): void
    {
        Voucher::create([
            'code' => 'TEKNOBARU',
            'discount_amount' => 50000,
            'usage_limit' => 100,
            'min_purchase' => 100000,
        ]);

        Voucher::create([
            'code' => 'DISKON10',
            'discount_amount' => 10000,
            'usage_limit' => 1000,
            'min_purchase' => 0,
        ]);
    }
}
