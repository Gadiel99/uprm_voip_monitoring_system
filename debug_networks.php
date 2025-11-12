<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== DEBUG NETWORKS ===\n\n";

// Total networks
$totalNetworks = DB::table('networks')->count();
echo "Total networks in database: $totalNetworks\n\n";

// Networks assigned to buildings
$assignedNetworkIds = DB::table('building_networks')
    ->distinct()
    ->pluck('network_id')
    ->toArray();
    
echo "Network IDs assigned to buildings (" . count($assignedNetworkIds) . "):\n";
print_r($assignedNetworkIds);
echo "\n";

// All network IDs
$allNetworkIds = DB::table('networks')->pluck('network_id')->toArray();
echo "All network IDs in system (" . count($allNetworkIds) . "):\n";
print_r($allNetworkIds);
echo "\n";

// Unmapped networks
$unmappedNetworkIds = array_diff($allNetworkIds, $assignedNetworkIds);
echo "Unmapped network IDs (" . count($unmappedNetworkIds) . "):\n";
print_r($unmappedNetworkIds);
echo "\n";

// Get unmapped network details
if (!empty($unmappedNetworkIds)) {
    echo "Unmapped network details:\n";
    $unmapped = DB::table('networks')
        ->whereIn('network_id', $unmappedNetworkIds)
        ->get(['network_id', 'subnet']);
    foreach ($unmapped as $net) {
        echo "  - Network ID: {$net->network_id}, Subnet: {$net->subnet}\n";
    }
} else {
    echo "✓ All networks are assigned to buildings!\n";
}

echo "\n=== CHECKING CONTROLLER QUERY ===\n\n";

// Simulate the controller query
$assignedNetworkIds2 = DB::table('building_networks')
    ->pluck('network_id')
    ->toArray();

$unmappedNetworks = DB::table('networks as n')
    ->leftJoin('devices as d', 'd.network_id', '=', 'n.network_id')
    ->whereNotIn('n.network_id', $assignedNetworkIds2)
    ->groupBy('n.network_id', 'n.subnet')
    ->selectRaw('n.network_id, n.subnet, COUNT(DISTINCT d.device_id) as total_devices')
    ->get();

echo "Query result - Unmapped networks: " . $unmappedNetworks->count() . "\n";
foreach ($unmappedNetworks as $net) {
    echo "  - {$net->subnet} (Network ID: {$net->network_id}, Devices: {$net->total_devices})\n";
}
