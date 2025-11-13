<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔄 Updating building coordinates from hardcoded values...\n\n";

// Hardcoded coordinates from the old system
$coordinates = [
    "Celis" => ["map_y" => 71.3, "map_x" => 78.3],
    "Stefani" => ["map_y" => 56.5, "map_x" => 82.5],
    "Biologia" => ["map_y" => 18.5, "map_x" => 72],
    "DeDiego" => ["map_y" => 78, "map_x" => 76.7],
    "Luchetti" => ["map_y" => 70, "map_x" => 86.4],
    "ROTC" => ["map_y" => 63.5, "map_x" => 85.4],
    "Adm.Empresas" => ["map_y" => 13, "map_x" => 33],
    "Musa" => ["map_y" => 78.5, "map_x" => 67],
    "Chardon" => ["map_y" => 58.3, "map_x" => 75.9],
    "Monzon" => ["map_y" => 75.8, "map_x" => 72.5],
    "Sanchez Hidalgo" => ["map_y" => 46.5, "map_x" => 70.1],
    "Fisica" => ["map_y" => 41, "map_x" => 76],
    "Geologia" => ["map_y" => 40, "map_x" => 78],
    "Ciencias Marinas" => ["map_y" => 39, "map_x" => 80],
    "Quimica" => ["map_y" => 40, "map_x" => 63],
    "Piñero" => ["map_y" => 85, "map_x" => 60.5],
    "Enfermeria" => ["map_y" => 51.5, "map_x" => 59],
    "Vagones" => ["map_y" => 48, "map_x" => 53],
    "Natatorio" => ["map_y" => 32.6, "map_x" => 30.5],
    "Centro Nuclear" => ["map_y" => 18.2, "map_x" => 86.5],
    "Coliseo" => ["map_y" => 64, "map_x" => 46],
    "Gimnacio" => ["map_y" => 66.7, "map_x" => 54.1],
    "Servicios Medicos" => ["map_y" => 71, "map_x" => 67],
    "Decanato de Estudiantes" => ["map_y" => 79, "map_x" => 80.5],
    "Oficina de Facultad" => ["map_y" => 49.7, "map_x" => 66],
    "Adm.Finca Alzamora" => ["map_y" => 62, "map_x" => 8],
    "Biblioteca" => ["map_y" => 62.5, "map_x" => 65.8],
    "Centro de Estudiantes" => ["map_y" => 64.8, "map_x" => 72.6],
    "Terrats" => ["map_y" => 48, "map_x" => 81],
    "Ing.Civil" => ["map_y" => 7.1, "map_x" => 59.8],
    "Ing.Industrial" => ["map_y" => 49, "map_x" => 78],
    "Ing.Quimica" => ["map_y" => 17.7, "map_x" => 55.7],
    "Ing.Agricola" => ["map_y" => 38.1, "map_x" => 50.9],
    "Edificio A (Hotel Colegial)" => ["map_y" => 26.9, "map_x" => 18.2],
    "Edificio B (Adm.Peq.Negocios y Oficina Adm)" => ["map_y" => 33, "map_x" => 17.6],
    "Edificio C (Oficina de Extension Agricola)" => ["map_y" => 26.8, "map_x" => 21.8],
    "Edificio D" => ["map_y" => 29.3, "map_x" => 20]
];

$updated = 0;
$notFound = 0;
$alreadySet = 0;

foreach ($coordinates as $buildingName => $coords) {
    // Check if building exists
    $building = DB::table('buildings')
        ->where('name', $buildingName)
        ->first();
    
    if (!$building) {
        echo "⚠️  Building not found: {$buildingName}\n";
        $notFound++;
        continue;
    }
    
    // Check if coordinates are already set (not default 0,0 or 50,50)
    if ($building->map_x != 0 && $building->map_y != 0 && 
        $building->map_x != 50 && $building->map_y != 50) {
        echo "ℹ️  {$buildingName} already has coordinates ({$building->map_x}, {$building->map_y})\n";
        $alreadySet++;
        continue;
    }
    
    // Update coordinates
    DB::table('buildings')
        ->where('building_id', $building->building_id)
        ->update([
            'map_x' => $coords['map_x'],
            'map_y' => $coords['map_y']
        ]);
    
    echo "✅ Updated {$buildingName}: X={$coords['map_x']}, Y={$coords['map_y']}\n";
    $updated++;
}

echo "\n📊 Summary:\n";
echo "   Updated: {$updated}\n";
echo "   Already set: {$alreadySet}\n";
echo "   Not found: {$notFound}\n";
echo "\n✨ Done!\n";
