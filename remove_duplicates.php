<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== FINDING DUPLICATES ===" . PHP_EOL . PHP_EOL;

$celis = DB::table('buildings')->where('name', 'Celis')->get(['building_id', 'name', 'map_x', 'map_y']);
echo "Celis duplicates (" . $celis->count() . "):" . PHP_EOL;
foreach ($celis as $c) {
    echo "  ID: {$c->building_id}, Name: {$c->name}, X: {$c->map_x}, Y: {$c->map_y}" . PHP_EOL;
}

echo PHP_EOL;

$stefani = DB::table('buildings')->where('name', 'Stefani')->get(['building_id', 'name', 'map_x', 'map_y']);
echo "Stefani duplicates (" . $stefani->count() . "):" . PHP_EOL;
foreach ($stefani as $s) {
    echo "  ID: {$s->building_id}, Name: {$s->name}, X: {$s->map_x}, Y: {$s->map_y}" . PHP_EOL;
}

echo PHP_EOL . "I will keep the FIRST occurrence of each and delete the duplicates." . PHP_EOL;

// Delete duplicates - keep the one with lowest building_id
if ($celis->count() > 1) {
    $keepCelis = $celis->first()->building_id;
    $deleteCelis = $celis->skip(1)->pluck('building_id')->toArray();
    
    echo PHP_EOL . "Keeping Celis ID: {$keepCelis}" . PHP_EOL;
    echo "Deleting Celis IDs: " . implode(', ', $deleteCelis) . PHP_EOL;
    
    // Delete from building_networks first
    DB::table('building_networks')->whereIn('building_id', $deleteCelis)->delete();
    // Delete from buildings
    DB::table('buildings')->whereIn('building_id', $deleteCelis)->delete();
    
    echo "✓ Celis duplicates deleted" . PHP_EOL;
}

if ($stefani->count() > 1) {
    $keepStefani = $stefani->first()->building_id;
    $deleteStefani = $stefani->skip(1)->pluck('building_id')->toArray();
    
    echo PHP_EOL . "Keeping Stefani ID: {$keepStefani}" . PHP_EOL;
    echo "Deleting Stefani IDs: " . implode(', ', $deleteStefani) . PHP_EOL;
    
    // Delete from building_networks first
    DB::table('building_networks')->whereIn('building_id', $deleteStefani)->delete();
    // Delete from buildings
    DB::table('buildings')->whereIn('building_id', $deleteStefani)->delete();
    
    echo "✓ Stefani duplicates deleted" . PHP_EOL;
}

echo PHP_EOL . "=== FINAL COUNT ===" . PHP_EOL;
echo "Total buildings now: " . DB::table('buildings')->count() . PHP_EOL;
