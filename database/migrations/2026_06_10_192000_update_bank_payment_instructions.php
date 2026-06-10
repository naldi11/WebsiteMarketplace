<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $instructions = [
            'bank_bri' => "1. Buka aplikasi m-banking (Brimo) atau pergi ke ATM BRI terdekat.\n2. Pilih menu Transfer -> Sesama BRI atau Transfer ke Bank Lain.\n3. Masukkan nomor rekening BRI admin yang tertera.\n4. Masukkan nominal transfer sesuai dengan \"Total Tagihan\".\n5. Simpan bukti transfer dan unggah ke aplikasi.",
            
            'bank_bca' => "1. Buka aplikasi m-BCA atau pergi ke ATM BCA terdekat.\n2. Pilih menu m-Transfer -> Antar Rekening atau Transfer ke Bank Lain.\n3. Masukkan nomor rekening BCA admin yang tertera.\n4. Masukkan nominal transfer sesuai dengan \"Total Tagihan\".\n5. Simpan bukti transfer dan unggah ke aplikasi.",
            
            'bank_mandiri' => "1. Buka aplikasi Livin' by Mandiri atau pergi ke ATM Mandiri terdekat.\n2. Pilih menu Transfer -> Ke rekening Mandiri atau Transfer ke Bank Lain.\n3. Masukkan nomor rekening Mandiri admin yang tertera.\n4. Masukkan nominal transfer sesuai dengan \"Total Tagihan\".\n5. Simpan bukti transfer dan unggah ke aplikasi.",
            
            'bank_bni' => "1. Buka aplikasi BNI Mobile Banking atau pergi ke ATM BNI terdekat.\n2. Pilih menu Transfer -> Sesama BNI atau Transfer ke Bank Lain.\n3. Masukkan nomor rekening BNI admin yang tertera.\n4. Masukkan nominal transfer sesuai dengan \"Total Tagihan\".\n5. Simpan bukti transfer dan unggah ke aplikasi.",
        ];

        foreach ($instructions as $code => $inst) {
            DB::table('payment_methods')
                ->where('code', $code)
                ->update(['instructions' => $inst]);
        }
    }

    public function down(): void
    {
        // No need to reverse as these are just instructions text
    }
};
