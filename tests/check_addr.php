<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach(\App\Models\UserAddress::all() as $addr) {
    echo "ID " . $addr->id . ": " . $addr->latitude . " | " . $addr->longitude . "\n";
}
