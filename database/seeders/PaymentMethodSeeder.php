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
                'admin_fee' => 4000,
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
                'admin_fee' => 4000,
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
                'admin_fee' => 4000,
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
                'admin_fee' => 4000,
                'admin_fee_percent' => 0,
                'is_active' => true,
                'sort_order' => 4,
            ],

            // E-Wallet
            [
                'code' => 'gopay',
                'name' => 'GoPay',
                'type' => 'ewallet',
                'icon' => 'gopay',
                'instructions' => "1. Klik tombol Bayar\n2. Anda akan diarahkan ke aplikasi Gojek\n3. Konfirmasi pembayaran di aplikasi",
                'admin_fee' => 0,
                'admin_fee_percent' => 2,
                'is_active' => true,
                'sort_order' => 10,
            ],
            [
                'code' => 'shopeepay',
                'name' => 'ShopeePay',
                'type' => 'ewallet',
                'icon' => 'shopeepay',
                'instructions' => "1. Klik tombol Bayar\n2. Scan QR Code dengan aplikasi Shopee\n3. Konfirmasi pembayaran",
                'admin_fee' => 0,
                'admin_fee_percent' => 2,
                'is_active' => true,
                'sort_order' => 11,
            ],
            [
                'code' => 'dana',
                'name' => 'DANA',
                'type' => 'ewallet',
                'icon' => 'dana',
                'instructions' => "1. Klik tombol Bayar\n2. Anda akan diarahkan ke aplikasi DANA\n3. Konfirmasi pembayaran di aplikasi",
                'admin_fee' => 0,
                'admin_fee_percent' => 2,
                'is_active' => true,
                'sort_order' => 12,
            ],
            [
                'code' => 'ovo',
                'name' => 'OVO',
                'type' => 'ewallet',
                'icon' => 'ovo',
                'instructions' => "1. Klik tombol Bayar\n2. Masukkan nomor HP OVO\n3. Konfirmasi pembayaran di aplikasi OVO",
                'admin_fee' => 0,
                'admin_fee_percent' => 2,
                'is_active' => true,
                'sort_order' => 13,
            ],

            // QRIS
            [
                'code' => 'qris',
                'name' => 'QRIS',
                'type' => 'qris',
                'icon' => 'qris',
                'instructions' => "1. Buka aplikasi e-wallet atau mobile banking\n2. Pilih menu Scan/QRIS\n3. Scan QR Code yang ditampilkan\n4. Konfirmasi pembayaran",
                'admin_fee' => 0,
                'admin_fee_percent' => 0.7,
                'is_active' => true,
                'sort_order' => 20,
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
