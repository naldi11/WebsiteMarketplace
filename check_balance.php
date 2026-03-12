<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Transaction;
use App\Models\SellerBalance;

// Assuming user ID 2 is the seller based on logs
$sellerId = 2;

$balance = SellerBalance::where('user_id', $sellerId)->first();
echo "--- SELLER BALANCE (UID: $sellerId) ---\n";
if ($balance) {
    echo "Available: " . $balance->available_balance . "\n";
    echo "Pending: " . $balance->pending_balance . "\n";
    echo "Total Earnings: " . $balance->total_earnings . "\n";
} else {
    echo "No balance record found.\n";
}

echo "\n--- TRANSACTIONS ---\n";
$transactions = Transaction::where('seller_id', $sellerId)->get();
foreach ($transactions as $tx) {
    echo "ID: " . $tx->id . " | Status: " . $tx->status . " | Seller Amount: " . $tx->seller_amount . " | Created: " . $tx->created_at . "\n";
}
