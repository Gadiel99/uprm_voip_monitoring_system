<?php

namespace App\Services;

use App\Models\Devices;
use App\Models\Extensions;
use App\Models\Networks;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use MongoDB\BSON\UTCDateTime as MongoUTCDateTime;
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
        // Get users from Postgres
        $users = $this->getUsersFromPostgres();

        // Get registrations from MongoDB
        $mongoRegistrations = $this->getRegistrationsFromMongo($since);
        
        // Log start of ETL
        Log::info('ETL started', [
            'postgres_users_count' => $users->count(),
            'mongo_registrations_count' => count($mongoRegistrations)
        ]);
        
        // Mark all existing devices as offline first
        Devices::query()->update(['status' => 'offline']);
        
        // Process and save data
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
        // Query Postgres users table
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
        // Build filter based on since parameter
        $filter = [];
        if ($since) {
            // Parse since time
            $timestamp = Carbon::parse($since);

            // Add filter for expirationTime
            $filter['expirationTime'] = ['$gte' => new MongoUTCDateTime($timestamp->timestamp * 1000)];
        }
        
        // Query MongoDB collection
        $cursor = DB::connection('mongodb')
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
        // Get all networks
        $networks = Networks::all();
        
        // Update stats for each network
        foreach ($networks as $network) {
            
            // Calculate total and offline devices
            $totalDevices = $network->devices()->count();

            // Calculate offline devices
            $offlineDevices = $network->devices()->where('status', 'offline')->count();
            
            // Update network record
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
    * Extract MAC address from MongoDB registration document
    * 
    * @param object $registration MongoDB document
    * @return string|null MAC address or null if not found
    */
    private function extractMacAddress( $registration): ?string
    {
        // Get instrument field from registration
        $instrument = $registration->instrument ?? null;
        
        if (!$instrument) {
            Log::warning("No instrument field found in registration");
            return 'unknown';
        }

        
        // Normalize MAC address to standard format (XX:XX:XX:XX:XX:XX)
        $mac = $this->normalizeMacAddress($instrument);
        
        return $mac ?: 'unknown';
    }

    /**
     * Normalize MAC address to standard format
     * 
     * @param string $mac Raw MAC address
     * @return string|null Normalized MAC address (XX:XX:XX:XX:XX:XX)
     */
    private function normalizeMacAddress(string $mac): ?string
    {
        // Remove common separators
        $cleaned = str_replace([':', '-', '.', ' '], '', $mac);
        
        // Check if valid MAC (12 hex characters)
        if (!preg_match('/^[0-9A-Fa-f]{12}$/', $cleaned)) {
            Log::warning("Invalid MAC address format: {$mac}");
            return null;
        }
        
        // Convert to uppercase and add colons
        $cleaned = strtoupper($cleaned);
        return implode(':', str_split($cleaned, 2));
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

        // Group registrations by IP address
        $deviceGroups = [];

        // Group registrations by IP address
        foreach ($mongoRegistrations as $registration) {

            // Get binding string from registration
            $binding = $registration->binding ?? null;

            // Extract IP address from binding
            $ipAddress = $this->extractIPFromBinding($binding);

            // Skip if no valid IP address
            if ($ipAddress) {
                if (!isset($deviceGroups[$ipAddress])) {
                    $deviceGroups[$ipAddress] = [];
                }
                $deviceGroups[$ipAddress][] = $registration;
            }
        }

        // Process each device group
        foreach ($deviceGroups as $ipAddress => $registrations) {

            // Get subnet for the device.
            $subnet = $this->getSubnetFromIP($ipAddress);
            
            // Find or create network
            $network = Networks::firstOrCreate(
                ['subnet' => $subnet],
                [
                    'offline_devices' => 0,
                    'total_devices' => 0,
                ]
            );

            // Get MAC address from first registration (instrument field)
            $macAddress = $this->extractMacAddress($registrations[0]);

            // Create or update device with MAC address
            $device = Devices::updateOrCreate(
                ['ip_address' => $ipAddress],
                [
                    'mac_address' => $macAddress,
                    'network_id' => $network->network_id,
                    'status' => 'online',
                    'is_critical' => false,
                ]
            );

            // Log creation or update
            if ($device->wasRecentlyCreated) {
                $devicesCreated++;
                Log::info("✅ Created device: IP={$ipAddress}, MAC={$macAddress}, Network={$subnet}, Status=ONLINE");
            } else {
                $devicesUpdated++;
                Log::info("🔄 Updated device: IP={$ipAddress}, MAC={$macAddress}, Network={$subnet}, Status=ONLINE");
            }

            // Process extensions for this device
            $extensionIds = [];
            foreach ($registrations as $registration) {
                $identity = $registration->identity ?? null;
                if (!$identity) continue;

                // Extract extension number from identity (e.g., "4444@domain.com")
                $extensionNumber = explode('@', $identity)[0];

                // Find user in Postgres users collection
                $user = $users->firstWhere('user_name', $extensionNumber);
                
                // If no user found, skip this extension.
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

                // Log creation or update
                if ($extension->wasRecentlyCreated) {
                    $extensionsCreated++;
                    Log::info("  ✅ Created extension: {$extensionNumber} ({$user->first_name} {$user->last_name})");
                } else {
                    $extensionsUpdated++;
                }

                $extensionIds[] = $extension->extension_id;
            }

            // Log syncing extensions
            if (!empty($extensionIds)) {
                $device->extensions()->sync($extensionIds);
                Log::info("  🔗 Synced " . count($extensionIds) . " extension(s) to device {$ipAddress}");
            }
        }

        
        // Final device status counts.
        $devicesOnline = Devices::query()->where('status', 'online')->count();

        // Count offline devices
        $devicesOffline = Devices::query()->where('status', 'offline')->count();
        // --- IGNORE ---
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