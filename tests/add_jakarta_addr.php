<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Create an address in Jakarta (far from Medan)
$user1 = \App\Models\UserAddress::first();
$userId = $user1->user_id;

$exists = \App\Models\UserAddress::where('user_id', $userId)
    ->where('full_address', 'LIKE', '%Jakarta%Sudirman%')
    ->first();

if (!$exists) {
    \App\Models\UserAddress::create([
        'user_id' => $userId,
        'label' => 'kantor',
        'recipient_name' => 'Test Jakarta',
        'phone' => '08123456789',
        'full_address' => 'Jl. Jend. Sudirman No. 1, Jakarta Pusat, DKI Jakarta',
        'latitude' => -6.2088,
        'longitude' => 106.8456,
        'is_default' => false,
    ]);
    echo "Created Jakarta address\n";
} else {
    echo "Jakarta address already exists\n";
}

// Show all addresses
$all = \App\Models\UserAddress::where('user_id', $userId)->get();
foreach($all as $a) {
    echo $a->id . " | lat=" . $a->latitude . " lng=" . $a->longitude . " | " . substr($a->full_address, 0, 50) . "\n";
}
