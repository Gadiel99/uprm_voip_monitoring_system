<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Building;
use App\Models\Network;
use App\Models\Devices;
use App\Models\Extensions;

class LoadTest3000PhonesSeeder extends Seeder
{
    /**
     * Seed 3,000 phones distributed across all buildings/networks for load testing
     * Preserves existing test data in Monzon (99) and Prueba (211) buildings
     * Preserves all critical devices marked from admin panel
     */
    public function run()
    {
        $this->command->info('🚀 Starting Load Test: Creating 3,000 phones...');
        $startTime = microtime(true);
        
        // Get networks that have critical devices (must be preserved)
        $criticalNetworkIds = Devices::where('is_critical', 1)
            ->pluck('network_id')
            ->unique()
            ->toArray();
        
        $criticalCount = Devices::where('is_critical', 1)->count();
        $this->command->info("🔒 Found {$criticalCount} critical devices in " . count($criticalNetworkIds) . " networks");
        
        // Get all buildings with their networks, EXCLUDING test buildings
        $buildings = Building::with('networks')
            ->whereNotIn('building_id', [99, 211]) // Preserve Monzon and Prueba
            ->get();
        
        if ($buildings->isEmpty()) {
            $this->command->error('❌ No buildings found (excluding Monzon and Prueba)');
            return;
        }
        
        // Get all networks from these buildings, EXCLUDING networks with critical devices
        $availableNetworks = [];
        foreach ($buildings as $building) {
            foreach ($building->networks as $network) {
                // Skip networks that contain critical devices
                if (in_array($network->network_id, $criticalNetworkIds)) {
                    $this->command->warn("  ⚠ Skipping {$building->name} - {$network->subnet} (contains critical devices)");
                    continue;
                }
                
                $availableNetworks[] = [
                    'network_id' => $network->network_id,
                    'subnet' => $network->subnet,
                    'building_name' => $building->name,
                ];
            }
        }
        
        if (empty($availableNetworks)) {
            $this->command->error('❌ No networks found in available buildings');
            return;
        }
        
        $this->command->info("📊 Found {$buildings->count()} buildings with " . count($availableNetworks) . " networks");
        $this->command->info("🔒 Preserving test data: Monzon, Prueba, and {$criticalCount} critical devices");
        
        $totalPhones = 3000;
        $phonesPerNetwork = ceil($totalPhones / count($availableNetworks));
        
        // Get the highest existing extension number to avoid conflicts
        $maxExtension = Extensions::max('extension_number') ?? 10000;
        $extensionCounter = $maxExtension + 1;
        
        // Load all existing MAC addresses to prevent duplicates
        $this->command->info("🔍 Loading existing MAC addresses...");
        $usedMacs = DB::table('devices')->pluck('mac_address')->flip()->all();
        $this->command->info("   Found " . count($usedMacs) . " existing MAC addresses");
        
        $phonesCreated = 0;
        $batchSize = 100;
        
        foreach ($availableNetworks as $index => $networkData) {
            if ($phonesCreated >= $totalPhones) {
                break;
            }
            
            $networkId = $networkData['network_id'];
            $subnet = $networkData['subnet'];
            $buildingName = $networkData['building_name'];
            
            // Calculate phones for this network
            $phonesForThisNetwork = min($phonesPerNetwork, $totalPhones - $phonesCreated);
            
            $this->command->info("📱 Creating {$phonesForThisNetwork} phones for {$buildingName} - Network {$subnet}...");
            
            // Parse subnet base (e.g., "10.100.101.0" -> "10.100.101")
            $subnetParts = explode('.', $subnet);
            $ipBase = "{$subnetParts[0]}.{$subnetParts[1]}.{$subnetParts[2]}";
            
            for ($batch = 0; $batch < $phonesForThisNetwork; $batch += $batchSize) {
                $devicesData = [];
                $extensionsData = [];
                $deviceExtensionsData = [];
                
                $currentBatchSize = min($batchSize, $phonesForThisNetwork - $batch);
                
                for ($i = 0; $i < $currentBatchSize; $i++) {
                    $phoneIndex = $batch + $i;
                    $ipLastOctet = 10 + ($phoneIndex % 240); // Range: 10-250
                    
                    // Generate unique MAC address (Polycom vendor prefix: 00:04:F2)
                    do {
                        $mac = sprintf('00:04:F2:%02X:%02X:%02X', 
                            rand(0, 255), 
                            rand(0, 255), 
                            rand(0, 255)
                        );
                    } while (isset($usedMacs[$mac]));
                    
                    $usedMacs[$mac] = 0; // Mark as used
                    
                    $ipAddress = "{$ipBase}.{$ipLastOctet}";
                    
                    // 95% online, 5% offline for realistic simulation
                    $status = (rand(1, 100) <= 95) ? 'online' : 'offline';
                    
                    // LoadTest devices are NEVER critical (only real devices can be marked critical)
                    $isCritical = 0;
                    
                    $devicesData[] = [
                        'mac_address' => $mac,
                        'ip_address' => $ipAddress,
                        'network_id' => $networkId,
                        'status' => $status,
                        'is_critical' => $isCritical,
                        'owner' => null,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                    
                    // Create corresponding extension
                    $extensionsData[] = [
                        'extension_number' => $extensionCounter,
                        'user_first_name' => "LoadTest",
                        'user_last_name' => "Phone{$extensionCounter}",
                        'devices_registered' => 1,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                    
                    $extensionCounter++;
                }
                
                // Insert devices batch
                DB::table('devices')->insert($devicesData);
                
                // Insert extensions batch
                DB::table('extensions')->insert($extensionsData);
                
                // Get the device IDs and extension IDs we just created
                $deviceIds = DB::table('devices')
                    ->whereIn('mac_address', array_column($devicesData, 'mac_address'))
                    ->pluck('device_id', 'mac_address')
                    ->toArray();
                
                $extensionIds = DB::table('extensions')
                    ->where('extension_number', '>=', $extensionCounter - $currentBatchSize)
                    ->where('extension_number', '<', $extensionCounter)
                    ->pluck('extension_id', 'extension_number')
                    ->toArray();
                
                // Create device-extension relationships
                foreach ($devicesData as $index => $deviceData) {
                    $deviceId = $deviceIds[$deviceData['mac_address']];
                    $extNumber = ($extensionCounter - $currentBatchSize) + $index;
                    $extensionId = $extensionIds[$extNumber];
                    
                    if ($deviceId && $extensionId) {
                        $deviceExtensionsData[] = [
                            'device_id' => $deviceId,
                            'extension_id' => $extensionId,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ];
                    }
                }
                
                // Insert device-extension relationships
                if (!empty($deviceExtensionsData)) {
                    DB::table('device_extensions')->insert($deviceExtensionsData);
                }
                
                $phonesCreated += $currentBatchSize;
                
                // Show progress
                $progress = round(($phonesCreated / $totalPhones) * 100, 1);
                $this->command->info("  ✓ Progress: {$phonesCreated}/{$totalPhones} ({$progress}%)");
            }
            
            // Update network device counts
            $network = Network::find($networkId);
            if ($network) {
                $network->updateDeviceCounts();
            }
        }
        
        $executionTime = round(microtime(true) - $startTime, 2);
        
        $this->command->info("\n" . str_repeat('=', 60));
        $this->command->info("✅ Load Test Seeding Complete!");
        $this->command->info(str_repeat('=', 60));
        $this->command->info("📊 Total Phones Created: {$phonesCreated}");
        $this->command->info("🏢 Buildings Used: {$buildings->count()}");
        $this->command->info("🌐 Networks Used: " . count($availableNetworks));
        $this->command->info("⏱️  Execution Time: {$executionTime}s");
        $this->command->info("🔒 Preserved: Monzon, Prueba, and {$criticalCount} critical devices");
        
        // Show final statistics
        $totalDevices = Devices::count();
        $totalExtensions = Extensions::count();
        $onlineDevices = Devices::where('status', 'online')->count();
        $offlineDevices = Devices::where('status', 'offline')->count();
        
        $this->command->info("\n📈 Database Statistics:");
        $this->command->info("  - Total Devices: {$totalDevices}");
        $this->command->info("  - Total Extensions: {$totalExtensions}");
        $this->command->info("  - Online: {$onlineDevices}");
        $this->command->info("  - Offline: {$offlineDevices}");
        $this->command->info(str_repeat('=', 60) . "\n");
    }
}
