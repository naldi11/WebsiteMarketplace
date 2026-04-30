<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Show all addresses
echo "=== Before update ===\n";
foreach(\App\Models\UserAddress::all() as $addr) {
    echo "ID " . $addr->id . ": " . $addr->full_address . " | lat=" . $addr->latitude . " | lng=" . $addr->longitude . "\n";
}

// Update addresses that have no coordinates with sample different locations
// Address ID 2: Jakarta area (far from Medan)
$addr2 = \App\Models\UserAddress::find(2);
if ($addr2 && !$addr2->latitude) {
    $addr2->update(['latitude' => -6.2088, 'longitude' => 106.8456]);
    echo "\n✅ Updated ID 2 with Jakarta coordinates (-6.2088, 106.8456)\n";
}

// Address ID 3: Bandung area
$addr3 = \App\Models\UserAddress::find(3);
if ($addr3 && !$addr3->latitude) {
    $addr3->update(['latitude' => -6.9175, 'longitude' => 107.6191]);
    echo "✅ Updated ID 3 with Bandung coordinates (-6.9175, 107.6191)\n";
}

echo "\n=== After update ===\n";
foreach(\App\Models\UserAddress::all() as $addr) {
    echo "ID " . $addr->id . ": " . $addr->full_address . " | lat=" . $addr->latitude . " | lng=" . $addr->longitude . "\n";
}
