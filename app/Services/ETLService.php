<?php

namespace App\Services;

use App\Models\Devices;
use App\Models\Extensions;
use App\Models\Networks;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use MongoDB\BSON\UTCDateTime;
use Illuminate\Support\Carbon;

class ETLService
{   
    /**
     * Main ETL function
     * @param mixed $since
     * @return array{devices_created: int, devices_offline: mixed, devices_online: mixed, devices_updated: int, extensions_created: int, extensions_updated: int}
     */
    public function run(?string $since = null): array
    {
        $users = $this->getUsersFromPostgres();
        $mongoRegistrations = $this->getRegistrationsFromMongo($since);
        
        Log::info('ETL started', [
            'postgres_users_count' => $users->count(),
            'mongo_registrations_count' => count($mongoRegistrations)
        ]);
        
        // Mark all existing devices as offline first
        Devices::query()->update(['status' => 'offline']);
        
        $result = $this->processAndSave($users, $mongoRegistrations);
        
        // Update network statistics
        $this->updateNetworkStats();
        
        return $result;
    }

    /* Extract Stage */
    
    /**
     * Extract from PostgresSQL Database
     * @return Collection<int, \stdClass>
     */
    private function getUsersFromPostgres(): Collection
    {
        return DB::connection('pgsql')
            ->table('users')
            ->select('first_name', 'last_name', 'user_name')
            ->get();
    }

    /**
     * Extrat from MongoDB Database
     * @param mixed $since
     * @return array
     */
    private function getRegistrationsFromMongo(?string $since = null): array
    {   
        $filter = [];
        if ($since) {
            $timestamp = Carbon::parse($since);
            $filter['expirationTime'] = ['$gte' => new UTCDateTime($timestamp->timestamp * 1000)];
        }
        
        $cursor = DB::connection('mongodb')
            ->getDatabase()
            ->selectCollection('registrar')
            ->find($filter);

        return iterator_to_array($cursor);
    }

    /* Transform & Load Stage */

    /**
     * Gets the network statistics and updates the networks table
     * @return void
     */
    private function updateNetworkStats()
    {
        $networks = Networks::all();
        
        foreach ($networks as $network) {
            $totalDevices = $network->devices()->count();
            $offlineDevices = $network->devices()->where('status', 'offline')->count();
            
            $network->update([
                'total_devices' => $totalDevices,
                'offline_devices' => $offlineDevices,
            ]);
            
            Log::info("📊 Updated network stats: {$network->subnet} - Total: {$totalDevices}, Offline: {$offlineDevices}");
        }
    }

    /**
     * Function to extract IP address from binding string.
     * @param string|null $binding
     * @return string|null
     */
    private function extractIPFromBinding(?string $binding): ?string
    {
        if (!$binding) return null;
        
        // Extract IP from binding like "sip:4444@10.100.147.103"
        if (preg_match('/@(\d+\.\d+\.\d+\.\d+)/', $binding, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    /**
     * Function to get subnet from IP address
     * @param string $ip
     * @return string
     */
    private function getSubnetFromIP(string $ip): string
    {
        // Extract first 3 octets for /24 subnet
        // Example: 10.100.147.103 -> 10.100.147.0/24
        $parts = explode('.', $ip);
        return "{$parts[0]}.{$parts[1]}.{$parts[2]}.0/24";
    }
    /**
     * Summary of processAndSave
     * @param \Illuminate\Support\Collection $users
     * @param array $mongoRegistrations
     * @return array{devices_created: int, devices_offline: mixed, devices_online: mixed, devices_updated: int, extensions_created: int, extensions_updated: int}
     */
    private function processAndSave(Collection $users, array $mongoRegistrations): array
    {
        $devicesCreated = 0;
        $devicesUpdated = 0;
        $extensionsCreated = 0;
        $extensionsUpdated = 0;

        // Group registrations by IP ADDRESS (each IP = one device)
        $deviceGroups = [];
        foreach ($mongoRegistrations as $registration) {
            $binding = $registration->binding ?? null;
            $ipAddress = $this->extractIPFromBinding($binding);
            
            if ($ipAddress) {
                if (!isset($deviceGroups[$ipAddress])) {
                    $deviceGroups[$ipAddress] = [];
                }
                $deviceGroups[$ipAddress][] = $registration;
            }
        }

        // Process each device that appears in registrar means that it's online.
        foreach ($deviceGroups as $ipAddress => $registrations) {
            // Determine network (subnet) from IP address
            $subnet = $this->getSubnetFromIP($ipAddress);
            
            // Find or create network
            $network = Networks::firstOrCreate(
                ['subnet' => $subnet],
                [
                    'offline_devices' => 0,
                    'total_devices' => 0,
                ]
            );

            // Create or update device - Status = ONLINE because it's in registrar
            $device = Devices::updateOrCreate(
                ['ip_address' => $ipAddress],
                [
                    'network_id' => $network->network_id,
                    'status' => 'online', // Device is online if it's in registrar
                ]
            );

            if ($device->wasRecentlyCreated) {
                $devicesCreated++;
                Log::info("✅ Created device: IP={$ipAddress}, Network={$subnet}, Status=ONLINE");
            } else {
                $devicesUpdated++;
                Log::info("🔄 Updated device: IP={$ipAddress}, Network={$subnet}, Status=ONLINE");
            }

            // Process extensions for this device
            $extensionIds = [];
            foreach ($registrations as $registration) {
                $identity = $registration->identity ?? null;
                
                if (!$identity) continue;

                $extensionNumber = explode('@', $identity)[0];
                $user = $users->firstWhere('user_name', $extensionNumber);
                
                if (!$user) {
                    Log::warning("No user found for extension {$extensionNumber}");
                    continue;
                }

                // Create or update extension
                $extension = Extensions::updateOrCreate(
                    ['extension_number' => $extensionNumber],
                    [
                        'user_first_name' => $user->first_name,
                        'user_last_name' => $user->last_name,
                    ]
                );

                if ($extension->wasRecentlyCreated) {
                    $extensionsCreated++;
                    Log::info("  ✅ Created extension: {$extensionNumber} ({$user->first_name} {$user->last_name})");
                } else {
                    $extensionsUpdated++;
                }

                $extensionIds[] = $extension->extension_id;
            }

            // Sync extensions to device (many-to-many relationship via device_extensions pivot)
            if (!empty($extensionIds)) {
                $device->extensions()->sync($extensionIds);
                Log::info("  🔗 Synced " . count($extensionIds) . " extension(s) to device {$ipAddress}");
            }
        }

        // Count device statistics
        $devicesOnline = Devices::where('status', 'online')->count();
        $devicesOffline = Devices::where('status', 'offline')->count();

        Log::info('ETL completed', [
            'devices_created' => $devicesCreated,
            'devices_updated' => $devicesUpdated,
            'extensions_created' => $extensionsCreated,
            'extensions_updated' => $extensionsUpdated,
            'devices_online' => $devicesOnline,
            'devices_offline' => $devicesOffline,
        ]);

        return [
            'devices_created' => $devicesCreated,
            'devices_updated' => $devicesUpdated,
            'extensions_created' => $extensionsCreated,
            'extensions_updated' => $extensionsUpdated,
            'devices_online' => $devicesOnline,
            'devices_offline' => $devicesOffline,
        ];
    }

    
}