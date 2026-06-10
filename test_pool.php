<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->boot();

$bLat = 3.5596;
$bLon = 98.6722;

// Simulasi 3 produk dengan koordinat berbeda
$products = [
    ['key' => 0, 'lat' => 3.5750, 'lon' => 98.6900],
    ['key' => 1, 'lat' => 3.5820, 'lon' => 98.7100],
    ['key' => 2, 'lat' => 3.5500, 'lon' => 98.6500],
];

$orderedKeys = [];
$orderedUrls = [];

foreach ($products as $p) {
    $sLat = $p['lat'];
    $sLon = $p['lon'];
    $orderedKeys[] = $p['key'];
    $orderedUrls[] = "http://router.project-osrm.org/route/v1/driving/{$bLon},{$bLat};{$sLon},{$sLat}?overview=false";
}

echo "Sending " . count($orderedUrls) . " concurrent OSRM Route requests...\n";

$roadDistances = [];
try {
    $responses = \Illuminate\Support\Facades\Http::pool(function ($pool) use ($orderedUrls) {
        $reqs = [];
        foreach ($orderedUrls as $url) {
            $reqs[] = $pool->timeout(6)->get($url);
        }
        return $reqs;
    });

    echo "Got " . count($responses) . " responses\n";

    foreach ($responses as $idx => $response) {
        if (!isset($orderedKeys[$idx])) {
            echo "idx={$idx} -> NO matching key!\n";
            continue;
        }
        $productKey = $orderedKeys[$idx];

        if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
            $json = $response->json();
            if (isset($json['code']) && $json['code'] === 'Ok' && !empty($json['routes'])) {
                $dist = round($json['routes'][0]['distance'] / 1000.0, 2);
                $roadDistances[$productKey] = $dist;
                echo "idx={$idx} -> product_key={$productKey} -> {$dist} km [OK]\n";
            } else {
                echo "idx={$idx} -> product_key={$productKey} -> OSRM error: code=" . ($json['code'] ?? 'unknown') . "\n";
            }
        } else {
            $class = get_class($response);
            echo "idx={$idx} -> product_key={$productKey} -> FAILED ({$class})\n";
        }
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

echo "\nroadDistances result:\n";
foreach ($roadDistances as $k => $v) {
    echo "  product[$k] = {$v} km\n";
}

echo "\nTest DONE. Expected 3 entries in roadDistances.\n";
