<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;
use App\Jobs\TransactionExpiryJob;

Schedule::job(new TransactionExpiryJob)->everyMinute();

// Auto-complete shipped orders yang sudah > 3 hari tanpa konfirmasi pembeli
// Jalan setiap jam (bisa ubah ke ->daily() di production)
Schedule::command('orders:auto-complete')->hourly();
