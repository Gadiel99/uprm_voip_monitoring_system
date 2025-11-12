<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== DATABASE COUNTS ===" . PHP_EOL . PHP_EOL;

$buildings = DB::table('buildings')->count();
echo "Buildings (markers): " . $buildings . PHP_EOL;

$networks = DB::table('networks')->count();
echo "Networks: " . $networks . PHP_EOL;

$devices = DB::table('devices')->count();
echo "Devices: " . $devices . PHP_EOL;

$bn_count = DB::table('building_networks')->count();
echo "Building-Network associations: " . $bn_count . PHP_EOL;

echo PHP_EOL;

$assigned_networks = DB::table('building_networks')->distinct()->count('network_id');
echo "Unique networks assigned to buildings: " . $assigned_networks . PHP_EOL;

$unassigned = DB::table('networks as n')
    ->whereNotExists(function($q) { 
        $q->select(DB::raw(1))
          ->from('building_networks as bn')
          ->whereRaw('bn.network_id = n.network_id'); 
    })
    ->count();
echo "Networks NOT assigned to any building: " . $unassigned . PHP_EOL;

echo PHP_EOL . "=== BREAKDOWN ===" . PHP_EOL . PHP_EOL;

// Show which buildings have which networks
$buildingsWithNetworks = DB::table('buildings as b')
    ->leftJoin('building_networks as bn', 'bn.building_id', '=', 'b.building_id')
    ->leftJoin('networks as n', 'n.network_id', '=', 'bn.network_id')
    ->select('b.name', DB::raw('COUNT(DISTINCT n.network_id) as network_count'))
    ->groupBy('b.building_id', 'b.name')
    ->orderBy('b.name')
    ->get();

echo "Networks per building:" . PHP_EOL;
foreach ($buildingsWithNetworks as $b) {
    echo "  - {$b->name}: {$b->network_count} networks" . PHP_EOL;
}
