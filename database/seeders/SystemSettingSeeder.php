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
                    "1. Biaya Layanan Platform: Setiap transaksi sukses akan dikenakan biaya layanan sebesar 10% dari total harga produk.\n" .
                    "2. Pilihan & Tarif Kendaraan Pengiriman (Skema A - Dibayar oleh Pembeli di Awal):\n" .
                    "   - Jemput Sendiri (Self-Pickup): Ongkos kirim Rp 0. Pembeli mengambil barang sendiri ke lokasi penjual.\n" .
                    "   - Kurir Motor (Untuk barang ringan/sedang s/d 25 kg, dimensi kecil):\n" .
                    "     * Jarak <= 5 km: Rp 10.000 (Flat).\n" .
                    "     * Jarak > 5 km: Rp 10.000 + Rp 3.000 per km tambahan.\n" .
                    "   - Becak (Bentor/Becak Barang untuk barang s/d 100 kg, ukuran sedang):\n" .
                    "     * Jarak <= 5 km: Rp 25.000 (Flat).\n" .
                    "     * Jarak > 5 km: Rp 25.000 + Rp 3.500 per km tambahan.\n" .
                    "   - Mobil Pickup (Untuk barang berat > 25 kg s/d 1000 kg, barang bekas besar, atau barang pindahan):\n" .
                    "     * Jarak <= 5 km: Rp 80.000 (Flat).\n" .
                    "     * Jarak > 5 km: Rp 80.000 + Rp 5.000 per km tambahan.\n" .
                    "   - Penentuan Opsi Kendaraan: Pembeli memilih opsi kendaraan saat checkout. Jika berat total barang > 25 kg atau produk memiliki kategori 'Barang Pindahan', sistem menyarankan/mewajibkan opsi Mobil Pickup.\n" .
                    "3. Penyelesaian Kendala Pengiriman & Perlindungan Penjual:\n" .
                    "   - Jika barang rusak/tidak sesuai saat diterima, Pembeli dilarang klik 'Pesanan Selesai' dan wajib mengeklik 'Ajukan Komplain' sebelum batas waktu berakhir untuk membekukan dana.\n" .
                    "   - Penjual Wajib mengunggah Foto Penyerahan (barang yang sudah terkemas aman di atas kendaraan/kurir) saat mengubah status transaksi menjadi 'Dikirim'.\n" .
                    "   - Jika ada komplain kerusakan fisik barang, Admin akan memeriksa Foto Penyerahan tersebut. Apabila terbukti barang rusak akibat kelalaian kurir pihak ketiga (GoSend/Maxim/Lalamove/Becak) setelah diserahkan dalam keadaan utuh, Penjual dilindungi dan dapat mengajukan klaim ganti rugi ke penyedia kurir terkait. Jika penjual tidak mengunggah Foto Penyerahan, penjual wajib menanggung kerugian.\n" .
                    "4. Pengembalian Barang: Jika komplain disetujui untuk pengembalian barang, Pembeli wajib mengirimkan kembali barang ke Penjual menggunakan kurir ber-resi sebelum refund diproses."
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
