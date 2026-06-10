<?php
// Test OSRM Route Service
$bLat = 3.5596; $bLon = 98.6722;
$sLat = 3.5750; $sLon = 98.6900;
$url = "http://router.project-osrm.org/route/v1/driving/{$bLon},{$bLat};{$sLon},{$sLat}?overview=false";
echo "URL: " . $url . PHP_EOL;
$ctx = stream_context_create(['http' => ['timeout' => 8]]);
$resp = @file_get_contents($url, false, $ctx);
if ($resp) {
    $json = json_decode($resp, true);
    if (isset($json['code']) && $json['code'] === 'Ok') {
        echo "OSRM Route OK! Jarak: " . round($json['routes'][0]['distance'] / 1000, 2) . " km" . PHP_EOL;
        echo "Duration: " . round($json['routes'][0]['duration'] / 60, 1) . " mnt" . PHP_EOL;
    } else {
        echo "OSRM Response: " . $resp . PHP_EOL;
    }
} else {
    echo "GAGAL request ke OSRM (timeout/network error)" . PHP_EOL;
}

// Test Http::pool() key mapping
echo PHP_EOL . "Test pool key mapping:" . PHP_EOL;
$orderedKeys = [0, 3, 7]; // simulasi keys sparse
$orderedUrls = [
    "http://router.project-osrm.org/route/v1/driving/{$bLon},{$bLat};{$sLon},{$sLat}?overview=false",
];

echo "orderedKeys[0] = " . $orderedKeys[0] . " (index 0 maps to product key 0)" . PHP_EOL;
echo "Fix: responses[0] -> orderedKeys[0] -> roadDistances[product_key]" . PHP_EOL;
