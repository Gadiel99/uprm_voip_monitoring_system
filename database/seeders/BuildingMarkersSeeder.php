<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Building;

class BuildingMarkersSeeder extends Seeder
{
    /**
     * Seed the buildings table with default campus markers
     */
    public function run(): void
    {
        $buildings = [
            ['name' => 'Celis', 'map_x' => 78.3, 'map_y' => 71.3, 'networks' => ['10.100.71.0']],
            ['name' => 'Stefani', 'map_x' => 82.5, 'map_y' => 56.5, 'networks' => ['10.100.56.0']],
            ['name' => 'Biologia', 'map_x' => 72, 'map_y' => 18.5, 'networks' => ['10.100.18.0']],
            ['name' => 'DeDiego', 'map_x' => 76.7, 'map_y' => 78, 'networks' => ['10.100.78.0']],
            ['name' => 'Luchetti', 'map_x' => 86.4, 'map_y' => 70, 'networks' => ['10.100.70.0']],
            ['name' => 'ROTC', 'map_x' => 85.4, 'map_y' => 63.5, 'networks' => ['10.100.63.0']],
            ['name' => 'Adm.Empresas', 'map_x' => 33, 'map_y' => 13, 'networks' => ['10.100.13.0']],
            ['name' => 'Musa', 'map_x' => 67, 'map_y' => 78.5, 'networks' => ['10.100.79.0']],
            ['name' => 'Chardon', 'map_x' => 75.9, 'map_y' => 58.3, 'networks' => ['10.100.58.0']],
            ['name' => 'Monzon', 'map_x' => 72.5, 'map_y' => 75.8, 'networks' => ['10.100.76.0']],
            ['name' => 'Sanchez Hidalgo', 'map_x' => 70.1, 'map_y' => 46.5, 'networks' => ['10.100.46.0']],
            ['name' => 'Fisica', 'map_x' => 76, 'map_y' => 41, 'networks' => ['10.100.41.0']],
            ['name' => 'Geologia', 'map_x' => 78, 'map_y' => 40, 'networks' => ['10.100.40.0']],
            ['name' => 'Ciencias Marinas', 'map_x' => 80, 'map_y' => 39, 'networks' => ['10.100.39.0']],
            ['name' => 'Quimica', 'map_x' => 63, 'map_y' => 40, 'networks' => ['10.100.40.0']],
            ['name' => 'Piñero', 'map_x' => 60.5, 'map_y' => 85, 'networks' => ['10.100.85.0']],
            ['name' => 'Enfermeria', 'map_x' => 59, 'map_y' => 51.5, 'networks' => ['10.100.51.0']],
            ['name' => 'Vagones', 'map_x' => 53, 'map_y' => 48, 'networks' => ['10.100.48.0']],
            ['name' => 'Natatorio', 'map_x' => 30.5, 'map_y' => 32.6, 'networks' => ['10.100.32.0']],
            ['name' => 'Centro Nuclear', 'map_x' => 86.5, 'map_y' => 18.2, 'networks' => ['10.100.18.0']],
            ['name' => 'Coliseo', 'map_x' => 46, 'map_y' => 64, 'networks' => ['10.100.64.0']],
            ['name' => 'Gimnacio', 'map_x' => 54.1, 'map_y' => 66.7, 'networks' => ['10.100.67.0']],
            ['name' => 'Servicios Medicos', 'map_x' => 67, 'map_y' => 71, 'networks' => ['10.100.71.0']],
            ['name' => 'Decanato de Estudiantes', 'map_x' => 80.5, 'map_y' => 79, 'networks' => ['10.100.79.0']],
            ['name' => 'Oficina de Facultad', 'map_x' => 66, 'map_y' => 49.7, 'networks' => ['10.100.49.0']],
            ['name' => 'Adm.Finca Alzamora', 'map_x' => 8, 'map_y' => 62, 'networks' => ['10.100.62.0']],
            ['name' => 'Biblioteca', 'map_x' => 65.8, 'map_y' => 62.5, 'networks' => ['10.100.62.0']],
            ['name' => 'Centro de Estudiantes', 'map_x' => 72.6, 'map_y' => 64.8, 'networks' => ['10.100.64.0']],
            ['name' => 'Terrats', 'map_x' => 81, 'map_y' => 48, 'networks' => ['10.100.48.0']],
            ['name' => 'Ing.Civil', 'map_x' => 59.8, 'map_y' => 7.1, 'networks' => ['10.100.7.0']],
            ['name' => 'Ing.Industrial', 'map_x' => 78, 'map_y' => 49, 'networks' => ['10.100.49.0']],
            ['name' => 'Ing.Quimica', 'map_x' => 55.7, 'map_y' => 17.7, 'networks' => ['10.100.17.0']],
            ['name' => 'Ing.Agricola', 'map_x' => 50.9, 'map_y' => 38.1, 'networks' => ['10.100.38.0']],
            ['name' => 'Edificio A (Hotel Colegial)', 'map_x' => 18.2, 'map_y' => 26.9, 'networks' => ['10.100.26.0']],
            ['name' => 'Edificio B (Adm.Peq.Negocios y Oficina Adm)', 'map_x' => 17.6, 'map_y' => 33, 'networks' => ['10.100.33.0']],
            ['name' => 'Edificio C (Oficina de Extension Agricola)', 'map_x' => 21.8, 'map_y' => 26.8, 'networks' => ['10.100.26.0']],
            ['name' => 'Edificio D', 'map_x' => 20, 'map_y' => 29.3, 'networks' => ['10.100.29.0']],
        ];

        foreach ($buildings as $buildingData) {
            // Create building
            $building = Building::create([
                'name' => $buildingData['name'],
                'map_x' => $buildingData['map_x'],
                'map_y' => $buildingData['map_y']
            ]);

            // Create networks and associate with building
            foreach ($buildingData['networks'] as $subnet) {
                // Check if network exists
                $network = DB::table('networks')
                    ->where('subnet', $subnet)
                    ->first();

                if (!$network) {
                    // Create new network
                    $networkId = DB::table('networks')->insertGetId([
                        'subnet' => $subnet,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                } else {
                    $networkId = $network->network_id;
                }

                // Associate network with building
                DB::table('building_networks')->insert([
                    'building_id' => $building->building_id,
                    'network_id' => $networkId
                ]);
            }
        }

        $this->command->info('✅ Seeded ' . count($buildings) . ' buildings with networks');
    }
}
