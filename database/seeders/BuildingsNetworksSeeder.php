<?php
// filepath: database/seeders/BuildingsNetworksSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Buildings;
use App\Models\Networks;

class BuildingsNetworksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🏢 Seeding UPRM buildings and networks...');
        $this->command->newLine();

        // Building definitions with their respective subnets
        // Each building can have multiple networks (many-to-many relationship)
        $buildingsData = [
            // Academic Buildings
            ['name' => 'Celis', 'subnets' => ['10.100.10.0/24']],
            ['name' => 'Stefani', 'subnets' => ['10.100.100.0/24', '10.100.147.0/24']], // Your test data
            ['name' => 'Biologia', 'subnets' => ['10.100.20.0/24']],
            ['name' => 'DeDiego', 'subnets' => ['10.100.30.0/24']],
            ['name' => 'Luchetti', 'subnets' => ['10.100.40.0/24']],
            ['name' => 'ROTC', 'subnets' => ['10.100.50.0/24']],
            ['name' => 'Adm.Empresas', 'subnets' => ['10.100.60.0/24']],
            ['name' => 'Musa', 'subnets' => ['10.100.70.0/24']],
            ['name' => 'Chardon', 'subnets' => ['10.100.80.0/24']],
            ['name' => 'Monzon', 'subnets' => ['10.100.90.0/24']],
            ['name' => 'Sanchez Hidalgo', 'subnets' => ['10.100.110.0/24']],
            ['name' => 'Fisica', 'subnets' => ['10.100.120.0/24']],
            ['name' => 'Geologia', 'subnets' => ['10.100.130.0/24']],
            ['name' => 'Ciencias Marinas', 'subnets' => ['10.100.140.0/24']],
            ['name' => 'Quimica', 'subnets' => ['10.100.150.0/24']],
            ['name' => 'Piñero', 'subnets' => ['10.100.160.0/24']],
            ['name' => 'Enfermeria', 'subnets' => ['10.100.170.0/24']],
            ['name' => 'Vagones', 'subnets' => ['10.100.180.0/24']],
            ['name' => 'Natatorio', 'subnets' => ['10.100.190.0/24']],
            ['name' => 'Centro Nuclear', 'subnets' => ['10.100.200.0/24']],
            ['name' => 'Coliseo', 'subnets' => ['10.100.210.0/24']],
            ['name' => 'Gimnacio', 'subnets' => ['10.100.220.0/24']],
            
            // Service Buildings
            ['name' => 'Servicios Medicos', 'subnets' => ['10.100.230.0/24']],
            ['name' => 'Decanato de Estudiantes', 'subnets' => ['10.100.240.0/24']],
            ['name' => 'Oficina de Facultad', 'subnets' => ['10.100.250.0/24']],
            ['name' => 'Adm.Finca Alzamora', 'subnets' => ['10.101.10.0/24']],
            ['name' => 'Biblioteca', 'subnets' => ['10.101.20.0/24']],
            ['name' => 'Centro de Estudiantes', 'subnets' => ['10.101.30.0/24']],
            ['name' => 'Terrats', 'subnets' => ['10.101.40.0/24']],
            
            // Engineering Buildings
            ['name' => 'Ing.Civil', 'subnets' => ['10.101.50.0/24']],
            ['name' => 'Ing.Industrial', 'subnets' => ['10.101.60.0/24']],
            ['name' => 'Ing.Quimica', 'subnets' => ['10.101.70.0/24']],
            ['name' => 'Ing.Agricola', 'subnets' => ['10.101.80.0/24']],
            
            // Administration Buildings
            ['name' => 'Edificio A (Hotel Colegial)', 'subnets' => ['10.101.90.0/24']],
            ['name' => 'Edificio B (Adm.Peq.Negocios y Oficina Adm)', 'subnets' => ['10.101.100.0/24']],
            ['name' => 'Edificio C (Oficina de Extension Agricola)', 'subnets' => ['10.101.110.0/24']],
            ['name' => 'Edificio D', 'subnets' => ['10.101.120.0/24']],
        ];

        $totalBuildings = 0;
        $totalNetworks = 0;
        $totalRelations = 0;

        foreach ($buildingsData as $buildingData) {
            // Create building
            $building = Buildings::create([
                'name' => $buildingData['name'],
            ]);
            $totalBuildings++;
            
            $this->command->info("✅ {$building->name}");

            // Create networks for this building
            $networkIds = [];
            foreach ($buildingData['subnets'] as $subnet) {
                $network = Networks::firstOrCreate(
                    ['subnet' => $subnet],
                    [
                        'offline_devices' => 0,
                        'total_devices' => 0,
                    ]
                );
                
                $networkIds[] = $network->network_id;
                
                if ($network->wasRecentlyCreated) {
                    $totalNetworks++;
                    $this->command->info("   📡 Created network: {$subnet}");
                }
            }

            // Attach networks to building (many-to-many via building_networks pivot)
            $building->networks()->attach($networkIds);
            $totalRelations += count($networkIds);
        }

        $this->command->newLine();
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('📊 MariaDB Seeding Summary:');
        $this->command->info("   🏢 Buildings created: {$totalBuildings}");
        $this->command->info("   📡 Networks created: {$totalNetworks}");
        $this->command->info("   🔗 Building-Network relations: {$totalRelations}");
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->newLine();
        
        $this->command->info('✅ MariaDB buildings and networks seeded successfully!');
    }
}