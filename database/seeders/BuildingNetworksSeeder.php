<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Building;

class BuildingNetworksSeeder extends Seeder
{
    /**
     * Seed the buildings table with map markers and their associated networks
     */
    public function run(): void
    {
        $buildings = [
            ['name' => 'Monzon', 'map_x' => 72.5, 'map_y' => 75.8, 'networks' => ['10.100.30.0', '10.100.33.0', '10.100.71.0', '10.100.100.0', '10.100.154.0', '10.100.155.0', '10.100.159.224']],
            ['name' => 'DeDiego', 'map_x' => 76.7, 'map_y' => 78, 'networks' => ['10.100.31.0']],
            ['name' => 'Centro de Estudiantes', 'map_x' => 72.6, 'map_y' => 64.8, 'networks' => ['10.100.32.0']],
            ['name' => 'Stefani', 'map_x' => 82.5, 'map_y' => 56.5, 'networks' => ['10.100.34.0', '10.100.35.0', '10.100.56.0', '10.100.57.0', '10.100.58.0', '10.100.59.0', '10.100.115.128', '10.100.115.192', '10.100.116.0']],
            ['name' => 'Piñero', 'map_x' => 60.5, 'map_y' => 85, 'networks' => ['10.100.36.0', '10.100.37.0']],
            ['name' => 'Adm.Empresas', 'map_x' => 33, 'map_y' => 13, 'networks' => ['10.100.38.0', '10.100.147.240', '10.100.159.64']],
            ['name' => 'MUSA', 'map_x' => 67, 'map_y' => 78.5, 'networks' => ['10.100.42.0', '10.100.115.224']],
            ['name' => 'Luchetti', 'map_x' => 86.4, 'map_y' => 70, 'networks' => ['10.100.61.0', '10.100.161.0']],
            ['name' => 'Ing.Quimica', 'map_x' => 55.7, 'map_y' => 17.7, 'networks' => ['10.100.62.0']],
            ['name' => 'Edificio B (Adm.Peq.Negocios y Oficina Adm)', 'map_x' => 17.6, 'map_y' => 33, 'networks' => ['10.100.63.0']],
            ['name' => 'Coliseo', 'map_x' => 46, 'map_y' => 64, 'networks' => ['10.100.64.0', '10.100.119.129']],
            ['name' => 'Centro Nuclear', 'map_x' => 86.5, 'map_y' => 18.2, 'networks' => ['10.100.115.64', '10.100.160.0']],
            ['name' => 'Ing.Civil', 'map_x' => 59.8, 'map_y' => 7.1, 'networks' => ['10.100.117.0', '10.100.153.0']],
            ['name' => 'Impresos', 'map_x' => 22.3, 'map_y' => 7.6, 'networks' => ['10.100.117.64']],
            ['name' => 'Artes plasticas', 'map_x' => 52, 'map_y' => 52, 'networks' => ['10.100.117.192']],
            ['name' => 'Edificio D', 'map_x' => 20, 'map_y' => 29.3, 'networks' => ['10.100.118.0', '10.100.162.0']],
            ['name' => 'Servicios Medicos', 'map_x' => 67, 'map_y' => 71, 'networks' => ['10.100.118.64']],
            ['name' => 'Natatorio', 'map_x' => 30.5, 'map_y' => 32.6, 'networks' => ['10.100.118.128']],
            ['name' => 'Cancha de tenis', 'map_x' => 35, 'map_y' => 35, 'networks' => ['10.100.118.192']],
            ['name' => 'ROTC', 'map_x' => 85.4, 'map_y' => 63.5, 'networks' => ['10.100.119.0']],
            ['name' => 'Oficina de Facultad', 'map_x' => 66, 'map_y' => 49.7, 'networks' => ['10.100.122.0']],
            ['name' => 'Geologia', 'map_x' => 78, 'map_y' => 40, 'networks' => ['10.100.123.0']],
            ['name' => 'Celis', 'map_x' => 78.3, 'map_y' => 71.3, 'networks' => ['10.100.124.0']],
            ['name' => 'Ciencias Marinas', 'map_x' => 80, 'map_y' => 39, 'networks' => ['10.100.125.0']],
            ['name' => 'Biologia', 'map_x' => 72, 'map_y' => 18.5, 'networks' => ['10.100.140.0']],
            ['name' => 'Decanato de Estudiantes', 'map_x' => 80.5, 'map_y' => 79, 'networks' => ['10.100.142.0']],
            ['name' => 'Enfermeria', 'map_x' => 59, 'map_y' => 51.5, 'networks' => ['10.100.143.0']],
            ['name' => 'Chardon', 'map_x' => 75.9, 'map_y' => 58.3, 'networks' => ['10.100.144.0']],
            ['name' => 'Darlington', 'map_x' => 2.4, 'map_y' => 31.2, 'networks' => ['10.100.146.0']],
            ['name' => 'Central Telefonica', 'map_x' => 11.7, 'map_y' => 10.7, 'networks' => ['10.100.147.0']],
            ['name' => 'Banda y Orquesta', 'map_x' => 84.4, 'map_y' => 82.0, 'networks' => ['10.100.147.32']],
            ['name' => 'Taller de Arte', 'map_x' => 54.7, 'map_y' => 40.7, 'networks' => ['10.100.147.64']],
            ['name' => 'Biblioteca', 'map_x' => 65.8, 'map_y' => 62.5, 'networks' => ['10.100.147.96', '10.100.147.160', '10.100.156.0']],
            ['name' => 'Adm.Finca Alzamora', 'map_x' => 8, 'map_y' => 62, 'networks' => ['10.100.147.128', '10.100.148.128']],
            ['name' => 'Prescolar', 'map_x' => 19.7, 'map_y' => 40.5, 'networks' => ['10.100.147.144']],
            ['name' => 'Ing.Agricola', 'map_x' => 50.9, 'map_y' => 38.1, 'networks' => ['10.100.148.0']],
            ['name' => 'CITAI', 'map_x' => 15.8, 'map_y' => 44.4, 'networks' => ['10.100.148.192']],
            ['name' => 'Terrats', 'map_x' => 81, 'map_y' => 48, 'networks' => ['10.100.149.0']],
            ['name' => 'Edificios y Terrenos', 'map_x' => 13.7, 'map_y' => 6.2, 'networks' => ['10.100.150.0']],
            ['name' => 'Ing.Industrial', 'map_x' => 78, 'map_y' => 49, 'networks' => ['10.100.151.0']],
            ['name' => 'Edificio A (Hotel Colegial)', 'map_x' => 18.2, 'map_y' => 26.9, 'networks' => ['10.100.158.0']],
            ['name' => 'Instituto de Recursos de Aguas', 'map_x' => 72.4, 'map_y' => 37.3, 'networks' => ['10.100.159.0']],
            ['name' => 'Siempre Vivas', 'map_x' => 69.5, 'map_y' => 35.9, 'networks' => ['10.100.159.96']],
            ['name' => 'CISA', 'map_x' => 86.7, 'map_y' =>11.3, 'networks' => ['10.100.159.128']],
            ['name' => 'Gimnasio', 'map_x' => 54.1, 'map_y' => 66.7, 'networks' => ['10.100.159.160']],
            ['name' => 'Vagones', 'map_x' => 53, 'map_y' => 48, 'networks' => ['10.100.159.192']],
            ['name' => 'CID', 'map_x' => 86.4, 'map_y' => 26.7, 'networks' => ['10.100.160.0']],
            ['name' => 'Sanchez Hidalgo', 'map_x' => 70.1, 'map_y' => 46.5, 'networks' => ['10.100.163.0']],
            ['name' => 'Fisica', 'map_x' => 76, 'map_y' => 41, 'networks' => ['10.100.164.0', '10.100.165.0']],
            ['name' => 'Quimica', 'map_x' => 63, 'map_y' => 40, 'networks' => ['10.100.166.0']],
            ['name' => 'Edificio C (Oficina de Extension Agricola)', 'map_x' => 21.8, 'map_y' => 26.8, 'networks' => ['10.100.60.0']],
        ];

        foreach ($buildings as $buildingData) {
            // Create or find building
            $building = Building::firstOrCreate(
                ['name' => $buildingData['name']],
                [
                    'map_x' => $buildingData['map_x'],
                    'map_y' => $buildingData['map_y']
                ]
            );

            // Create networks and associate with building
            foreach ($buildingData['networks'] as $subnet) {
                // Create or find network
                $network = DB::table('networks')
                    ->where('subnet', $subnet)
                    ->first();

                if (!$network) {
                    $networkId = DB::table('networks')->insertGetId([
                        'subnet' => $subnet,
                        'total_devices' => 0,
                        'offline_devices' => 0,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                } else {
                    $networkId = $network->network_id;
                }

                // Associate network with building (avoid duplicates)
                $exists = DB::table('building_networks')
                    ->where('building_id', $building->building_id)
                    ->where('network_id', $networkId)
                    ->exists();

                if (!$exists) {
                    DB::table('building_networks')->insert([
                        'building_id' => $building->building_id,
                        'network_id' => $networkId
                    ]);
                }
            }
        }

        $this->command->info('✅ Seeded ' . count($buildings) . ' buildings with map coordinates and networks');
    }
}
