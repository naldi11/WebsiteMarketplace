<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            // Bank Transfer
            [
                'code' => 'bank_bca',
                'name' => 'BCA Virtual Account',
                'type' => 'bank_transfer',
                'icon' => 'bca',
                'instructions' => "1. Buka aplikasi BCA Mobile atau ATM\n2. Pilih menu Transfer\n3. Pilih Virtual Account\n4. Masukkan nomor VA\n5. Konfirmasi pembayaran",
                'admin_fee' => 0,
                'admin_fee_percent' => 0,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'code' => 'bank_mandiri',
                'name' => 'Mandiri Virtual Account',
                'type' => 'bank_transfer',
                'icon' => 'mandiri',
                'instructions' => "1. Buka aplikasi Livin' by Mandiri atau ATM\n2. Pilih menu Bayar\n3. Pilih Multi Payment\n4. Masukkan nomor VA\n5. Konfirmasi pembayaran",
                'admin_fee' => 0,
                'admin_fee_percent' => 0,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'code' => 'bank_bni',
                'name' => 'BNI Virtual Account',
                'type' => 'bank_transfer',
                'icon' => 'bni',
                'instructions' => "1. Buka aplikasi BNI Mobile atau ATM\n2. Pilih menu Transfer\n3. Pilih Virtual Account\n4. Masukkan nomor VA\n5. Konfirmasi pembayaran",
                'admin_fee' => 0,
                'admin_fee_percent' => 0,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'code' => 'bank_bri',
                'name' => 'BRI Virtual Account',
                'type' => 'bank_transfer',
                'icon' => 'bri',
                'instructions' => "1. Buka aplikasi BRImo atau ATM\n2. Pilih menu Pembayaran\n3. Pilih BRIVA\n4. Masukkan nomor VA\n5. Konfirmasi pembayaran",
                'admin_fee' => 0,
                'admin_fee_percent' => 0,
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::updateOrCreate(
                ['code' => $method['code']],
                $method
            );
        }
    }
}
