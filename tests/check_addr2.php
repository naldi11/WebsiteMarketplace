<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$addresses = \App\Models\UserAddress::all();
foreach($addresses as $addr) {
    echo $addr->id . " | " . round($addr->latitude, 4) . " | " . round($addr->longitude, 4) . " | " . substr($addr->full_address, 0, 40) . "\n";
}
