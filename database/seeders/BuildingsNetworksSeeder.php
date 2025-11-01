<?php

/**
 * @file BuildingsNetworksSeeder.php
 * @brief Database seeder for UPRM buildings and their associated network infrastructure
 * @details This seeder populates the buildings and networks tables with real UPRM campus
 *          building data and their corresponding network subnets. It establishes the
 *          many-to-many relationships between buildings and networks through the pivot table.
 * @author UPRM VoIP Monitoring System Team
 * @date October 30, 2025
 * @version 1.0
 * @filepath database/seeders/BuildingsNetworksSeeder.php
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Buildings;
use App\Models\Networks;

/**
 * @class BuildingsNetworksSeeder
 * @brief Seeder class for populating buildings and networks with UPRM campus data
 * @details This seeder creates a comprehensive dataset of UPRM campus buildings and their
 *          associated network infrastructure. It handles the creation of buildings, networks,
 *          and their many-to-many relationships while providing detailed progress feedback.
 *          
 *          Seeded Data:
 *          - 37 UPRM campus buildings (academic, service, engineering, administration)
 *          - Network subnets in 10.100.x.x and 10.101.x.x ranges
 *          - Building-network relationships via pivot table
 *          
 *          Categories:
 *          - Academic Buildings: Celis, Stefani, Biologia, etc.
 *          - Service Buildings: Medical Services, Student Dean's Office, etc.
 *          - Engineering Buildings: Civil, Industrial, Chemical, Agricultural
 *          - Administration Buildings: Hotel Colegial, Business Administration, etc.
 *          
 * @extends Illuminate\Database\Seeder
 * @author Gadiel J. De Jesus Martinez - Triatek
 * @date October 30, 2025
 */

class BuildingsNetworksSeeder extends Seeder
{
    /**
     * @brief Executes the database seeding process for buildings and networks
     * @details This method performs the complete seeding operation for UPRM buildings
     *          and their associated network infrastructure. The process includes:
     *          
     *          1. Building Data Definition: Defines array of buildings with their subnets
     *          2. Iterative Creation: Creates buildings and networks in database
     *          3. Relationship Establishment: Links buildings to networks via pivot table
     *          4. Progress Tracking: Maintains counters for created entities
     *          5. Console Feedback: Provides detailed progress information
     *          6. Summary Report: Displays final statistics
     *          
     *          The seeder uses firstOrCreate() for networks to prevent duplicates,
     *          ensuring idempotent execution for development environments.
     * 
     * @return void
     * 
     * @throws \Illuminate\Database\QueryException If database operations fail
     * @throws \Exception If building or network creation encounters errors
     * 
     * @see App\Models\Buildings
     * @see App\Models\Networks
     * @author Gadiel J. De Jesus Martinez - Triatek
     * @date October 30, 2025
     */
    public function run(): void
    {
        // Display initial seeding message to console
        if ($this->command) {
            $this->command->info('🏢 Seeding UPRM buildings and networks...');
            $this->command->newLine();
        }

        /*
         * UPRM Campus Buildings Data Structure
         * 
         * This array contains the comprehensive mapping of UPRM campus buildings
         * to their respective network subnets. The structure follows:
         * - Building name as used in campus directory
         * - Associated subnets in CIDR notation
         * 
         * Network Architecture:
         * - 10.100.x.x/24: Primary academic and service buildings
         * - 10.101.x.x/24: Engineering and administrative buildings
         * 
         * Each building entry supports multiple subnets to accommodate
         * complex network topologies and building expansions.
         */
        $buildingsData = [
            /*
             * Academic Buildings Section
             * These buildings house classrooms, laboratories, and research facilities
             */
            ['name' => 'Celis', 'subnets' => ['10.100.10.0/24']],
            ['name' => 'Stefani', 'subnets' => ['10.100.100.0/24', '10.100.147.0/24']], // Multiple subnets for testing
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
            
            /*
             * Service Buildings Section
             * These buildings provide student and administrative services
             */
            ['name' => 'Servicios Medicos', 'subnets' => ['10.100.230.0/24']],
            ['name' => 'Decanato de Estudiantes', 'subnets' => ['10.100.240.0/24']],
            ['name' => 'Oficina de Facultad', 'subnets' => ['10.100.250.0/24']],
            ['name' => 'Adm.Finca Alzamora', 'subnets' => ['10.101.10.0/24']],
            ['name' => 'Biblioteca', 'subnets' => ['10.101.20.0/24']],
            ['name' => 'Centro de Estudiantes', 'subnets' => ['10.101.30.0/24']],
            ['name' => 'Terrats', 'subnets' => ['10.101.40.0/24']],
            
            /*
             * Engineering Buildings Section
             * These buildings house engineering departments and laboratories
             */
            ['name' => 'Ing.Civil', 'subnets' => ['10.101.50.0/24']],
            ['name' => 'Ing.Industrial', 'subnets' => ['10.101.60.0/24']],
            ['name' => 'Ing.Quimica', 'subnets' => ['10.101.70.0/24']],
            ['name' => 'Ing.Agricola', 'subnets' => ['10.101.80.0/24']],
            
            /*
             * Administration Buildings Section
             * These buildings house administrative offices and special facilities
             */
            ['name' => 'Edificio A (Hotel Colegial)', 'subnets' => ['10.101.90.0/24']],
            ['name' => 'Edificio B (Adm.Peq.Negocios y Oficina Adm)', 'subnets' => ['10.101.100.0/24']],
            ['name' => 'Edificio C (Oficina de Extension Agricola)', 'subnets' => ['10.101.110.0/24']],
            ['name' => 'Edificio D', 'subnets' => ['10.101.120.0/24']],
        ];

        /*
         * Statistics tracking variables
         * These counters track the seeding progress for summary reporting
         */
        $totalBuildings = 0;  // Count of buildings created
        $totalNetworks = 0;   // Count of networks created (excludes existing)
        $totalRelations = 0;  // Count of building-network relationships created

        /*
         * Main Seeding Loop
         * 
         * Iterates through each building definition to create the building
         * and its associated networks, then establishes the relationships
         */
        foreach ($buildingsData as $buildingData) {
            /*
             * Building Creation Process
             * Creates a new building record in the database
             */
            $building = Buildings::create([
                'name' => $buildingData['name'],
            ]);
            
            // Increment building counter for statistics
            $totalBuildings++;
            
            // Display progress feedback to console
            if ($this->command) {
                $this->command->info("✅ {$building->name}");
            }

            /*
             * Network Creation and Association Process
             * 
             * For each subnet associated with the current building:
             * 1. Create or retrieve existing network record
             * 2. Collect network IDs for relationship establishment
             * 3. Track creation statistics
             */
            $networkIds = [];
            foreach ($buildingData['subnets'] as $subnet) {
                /*
                 * Use firstOrCreate to ensure idempotent seeding
                 * This prevents duplicate network creation on multiple runs
                 */
                $network = Networks::firstOrCreate(
                    ['subnet' => $subnet],  // Search criteria
                    [                       // Default values for new records
                        'offline_devices' => 0,
                        'total_devices' => 0,
                    ]
                );
                
                // Collect network ID for relationship creation
                $networkIds[] = $network->network_id;
                
                // Update statistics only for newly created networks
                if ($network->wasRecentlyCreated) {
                    $totalNetworks++;
                    if ($this->command) {
                        $this->command->info("   📡 Created network: {$subnet}");
                    }
                }
            }

            /*
             * Relationship Establishment
             * 
             * Attach all networks to the current building using the
             * many-to-many relationship through the building_networks pivot table
             */
            $building->networks()->attach($networkIds);
            $totalRelations += count($networkIds);
        }

        /*
         * Seeding Summary and Completion Report
         * 
         * Display comprehensive statistics about the seeding operation
         * to provide feedback on the process success and scope
         */
        if ($this->command) {
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
}