<?php
// Test langsung OSRM pool tanpa Laravel framework - pakai curl multi
$bLat = 3.5596;
$bLon = 98.6722;

// Simulasi produk dengan koordinat
$products = [
    0 => ['lat' => 3.5750, 'lon' => 98.6900],
    1 => ['lat' => 3.5820, 'lon' => 98.7100],
    2 => ['lat' => 3.5500, 'lon' => 98.6500],
];

$orderedKeys = [];
$orderedUrls = [];

foreach ($products as $key => $p) {
    $sLat = $p['lat'];
    $sLon = $p['lon'];
    if ($sLat && $sLon) {
        $orderedKeys[] = $key;
        $orderedUrls[] = "http://router.project-osrm.org/route/v1/driving/{$bLon},{$bLat};{$sLon},{$sLat}?overview=false";
    }
}

echo "Mengirim " . count($orderedUrls) . " OSRM Route requests (curl_multi)...\n";

// Gunakan curl_multi untuk concurrent requests
$mh = curl_multi_init();
$handles = [];

foreach ($orderedUrls as $idx => $url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
    curl_multi_add_handle($mh, $ch);
    $handles[$idx] = $ch;
}

$running = null;
do {
    curl_multi_exec($mh, $running);
    curl_multi_select($mh);
} while ($running > 0);

$roadDistances = [];
foreach ($handles as $idx => $ch) {
    $responseBody = curl_multi_getcontent($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_multi_remove_handle($mh, $ch);
    curl_close($ch);

    if (!isset($orderedKeys[$idx])) continue;
    $productKey = $orderedKeys[$idx];

    if ($httpCode === 200 && $responseBody) {
        $json = json_decode($responseBody, true);
        if (isset($json['code']) && $json['code'] === 'Ok' && !empty($json['routes'])) {
            $dist = round($json['routes'][0]['distance'] / 1000.0, 2);
            $roadDistances[$productKey] = $dist;
            echo "idx={$idx} -> product_key={$productKey} -> {$dist} km [OK]\n";
        } else {
            echo "idx={$idx} -> product_key={$productKey} -> OSRM error\n";
        }
    } else {
        echo "idx={$idx} -> product_key={$productKey} -> HTTP {$httpCode} FAILED\n";
    }
}

curl_multi_close($mh);

echo "\nroadDistances:\n";
foreach ($roadDistances as $k => $v) {
    echo "  products[$k] = {$v} km\n";
}
echo "\nExpected 3 entries. Got: " . count($roadDistances) . "\n";

// Verifikasi key mapping benar:
// Harusnya: products[0] = ~2.43km, products[1] = ~3.5km, products[2] = ~2.0km
// Jika mapping salah: products[2] akan dapat jarak yang harusnya milik products[0]
