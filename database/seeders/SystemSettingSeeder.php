<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\SystemSetting::updateOrCreate(
            ['key' => 'terms_and_conditions'],
            [
                'description' => 'Syarat dan Ketentuan Layanan',
                'value' => "Syarat & Ketentuan Penggunaan Platform:\n\n" .
                    "1. Biaya Layanan Platform: Setiap transaksi akan dikenakan biaya layanan sebesar 10% dari total harga produk.\n" .
                    "2. Perhitungan Ongkos Kirim:\n" .
                    "   - Jarak maksimal 5 km: Rp 10.000 (Flat).\n" .
                    "   - Jarak > 5 km: Rp 10.000 + Rp 3.000 per km tambahan.\n" .
                    "   - Ketentuan Berat: Tarif normal berlaku untuk berat s/d 25 kg.\n" .
                    "   - Kelebihan Berat: Jika berat melebihi 25 kg, ongkos kirim akan dikalikan sesuai kelipatan (Contoh: 26 kg = 2x ongkir, 51 kg = 3x ongkir).\n" .
                    "3. Kewajiban Penjual: Penjual wajib mengunggah bukti pengiriman yang sah (foto resi atau foto penyerahan barang) saat mengubah status menjadi 'Dikirim'.\n" .
                    "4. Keamanan: Harap selalu melakukan transaksi di dalam platform untuk keamanan Anda."
            ]
        );

        \App\Models\SystemSetting::updateOrCreate(
            ['key' => 'privacy_policy'],
            [
                'description' => 'Kebijakan Privasi',
                'value' => "Kebijakan Privasi:\n\n" .
                    "Kami menghargai privasi Anda. Data lokasi (Latitude & Longitude) Anda digunakan semata-mata untuk keperluan perhitungan biaya pengiriman yang akurat antara Penjual dan Pembeli.\n" .
                    "Data pribadi Anda tidak akan dibagikan kepada pihak ketiga tanpa persetujuan Anda, kecuali untuk keperluan proses pengiriman barang."
            ]
        );

        \App\Models\SystemSetting::updateOrCreate(
            ['key' => 'admin_whatsapp'],
            [
                'description' => 'Nomor WhatsApp Admin (untuk bantuan/laporan)',
                'value' => '628123456789'
            ]
        );
    }
}
