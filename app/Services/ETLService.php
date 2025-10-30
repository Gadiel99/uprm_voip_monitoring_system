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
    // Metrics to track during ETL process
    private $metrics = [
        'devices_created' => 0,
        'devices_updated' => 0,
        'extensions_created' => 0,
        'extensions_updated' => 0,
        'devices_online' => 0,
        'devices_offline' => 0,
    ];
    
    /**
     * Run the ETL process
     * @return array The metrics of the ETL process
     */
    public function run(?Carbon $since = null): array
    {
        try {
            Log::info('Starting ETL process', ['since' => $since]);
            
            // Reset metrics
            $this->metrics = [
                'devices_created' => 0,
                'devices_updated' => 0,
                'extensions_created' => 0,
                'extensions_updated' => 0,
                'devices_online' => 0,
                'devices_offline' => 0,
            ];
            
            // Extract data from MongoDB and PostgreSQL
            $registrations = $this->getRegistrationsFromMongo($since);
            $users = $this->getUsersFromPostgres();
            
            Log::info('Data extracted', [
                'registrations_count' => $registrations->count(),
                'users_count' => $users->count()
            ]);
            
            // Transform and Load
            $this->processAndSave($users, $registrations->toArray());
            
            // Update network counts
            $this->updateNetworkCounts();
            
            Log::info('ETL process completed successfully', $this->metrics);
            
            return $this->metrics;
            
        } catch (\Exception $e) {
            Log::error('ETL process failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /* Extract Phase */

    /**
     * Summary of getRegistrationsFromMongo
     * @param mixed $since
     * @return Collection<TKey, TValue>
     */
    private function getRegistrationsFromMongo(?Carbon $since = null): Collection
    {   
        $query = [];
        
        if ($since) {
            $query['timestamp'] = [
                '$gte' => new MongoUTCDateTime($since->timestamp * 1000)
            ];
        }
        
        $registrations = DB::connection('mongodb')
            ->selectCollection('registrar')
            ->find($query)
            ->toArray();
        
        return collect($registrations);
    }

    /**
     * Function to get the data from users table from PostgreSQL
     * @return Collection<TKey, TValue>
    */
    private function getUsersFromPostgres(): Collection
    {   
        return DB::connection('pgsql')
            ->table('users')
            ->select('first_name', 'last_name', 'user_name')
            ->whereNotNull('first_name')
            ->get();
    }


    /* Transform and Load Phase */

    /**
     * Extract IP address from binding string
     */
    private function extractIpFromBinding(string $binding): ?string
    {
        if (preg_match('/(\d+\.\d+\.\d+\.\d+)/', $binding, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Format MAC address with colons
     */
    private function formatMacAddress(string $mac): string
    {
        $mac = strtoupper(preg_replace('/[^a-fA-F0-9]/', '', $mac));
        
        if (strlen($mac) === 12) {
            return implode(':', str_split($mac, 2));
        }
        
        return $mac;
    }

    /**
     * Find or create network based on IP address
     */
    private function findOrCreateNetwork(string $ipAddress): ?Networks
    {
        $ipParts = explode('.', $ipAddress);
        
        if (count($ipParts) !== 4) {
            return null;
        }
        
        $subnet = "{$ipParts[0]}.{$ipParts[1]}.{$ipParts[2]}.0/24";
        
        return Networks::firstOrCreate(
            ['subnet' => $subnet],
            [
                'building_id' => 1, // Default building
                'total_devices' => 0,
                'offline_devices' => 0,
            ]
        );
    }

    /**
     * Update network device counts
     */
    private function updateNetworkCounts(): void
    {
        $networks = Networks::all();
        
        foreach ($networks as $network) {
            $network->updateDeviceCounts();
            
            Log::info('Updated network counts', [
                'network_id' => $network->network_id,
                'total_devices' => $network->total_devices,
                'offline_devices' => $network->offline_devices
            ]);
        }
    }
    
    /**
     * Get metrics
     */
    public function getMetrics(): array
    {
        return $this->metrics;
    }


    /**
     * Process registrations and save to PostgreSQL
     * @param \Illuminate\Support\Collection $users
     * @param array $registrations
     * @return void
     */
    private function processAndSave(Collection $users, array $registrations): void
    {
        $processedExtensions = []; // Track extensions and their device counts
        
        foreach ($registrations as $registration) {
            try {
                // Extract data from registration
                $identity = $registration->identity ?? null;
                $binding = $registration->binding ?? null;
                $instrument = $registration->instrument ?? null;
                $expired = $registration->expired ?? true;
                
                if (!$identity || !$binding || !$instrument) {
                    Log::warning('Incomplete registration data', [
                        'identity' => $identity,
                        'binding' => $binding,
                        'instrument' => $instrument
                    ]);
                    continue;
                }
                
                // Extract extension number and IP address
                $extensionNumber = explode('@', $identity)[0];
                $ipAddress = $this->extractIpFromBinding($binding);
                
                if (!$ipAddress) {
                    Log::warning('Could not extract IP address', ['binding' => $binding]);
                    continue;
                }
                
                // Find matching user
                $user = $users->firstWhere('user_name', $extensionNumber);
                
                if (!$user) {
                    Log::warning('No user found for extension', ['extension' => $extensionNumber]);
                    continue;
                }
                
                // Determine device status
                $status = $expired ? 'offline' : 'online';
                
                // Update metrics
                if ($status === 'online') {
                    $this->metrics['devices_online']++;
                } else {
                    $this->metrics['devices_offline']++;
                }
                
                // Format MAC address (add colons)
                $formattedMac = $this->formatMacAddress($instrument);
                
                // Find or determine network
                $network = $this->findOrCreateNetwork($ipAddress);
                
                if (!$network) {
                    Log::warning('Could not determine network', ['ip' => $ipAddress]);
                    continue;
                }
                
                // Check if device exists
                $deviceExists = Devices::where('mac_address', $formattedMac)->exists();
                
                // Create or update device
                $device = Devices::updateOrCreate(
                    [
                        'mac_address' => $formattedMac,
                    ],
                    [
                        'ip_address' => $ipAddress,
                        'network_id' => $network->network_id,
                        'status' => $status,
                        'is_critical' => false,
                    ]
                );
                
                // Update metrics
                if ($deviceExists) {
                    $this->metrics['devices_updated']++;
                } else {
                    $this->metrics['devices_created']++;
                }
                
                Log::info('Device processed', [
                    'device_id' => $device->device_id,
                    'ip' => $ipAddress,
                    'mac' => $formattedMac,
                    'status' => $status,
                    'action' => $deviceExists ? 'updated' : 'created'
                ]);
                
                // Check if extension exists
                $extensionExists = Extensions::where('extension_number', $extensionNumber)->exists();
                
                // Create or update extension
                $extension = Extensions::updateOrCreate(
                    ['extension_number' => $extensionNumber],
                    [
                        'user_first_name' => $user->first_name,
                        'user_last_name' => $user->last_name,
                    ]
                );
                
                // Update metrics
                if ($extensionExists) {
                    $this->metrics['extensions_updated']++;
                } else {
                    $this->metrics['extensions_created']++;
                }
                
                // Track this extension's device registrations
                if (!isset($processedExtensions[$extensionNumber])) {
                    $processedExtensions[$extensionNumber] = [
                        'extension' => $extension,
                        'devices' => []
                    ];
                }
                
                // Add device to this extension's list (avoid duplicates)
                if (!in_array($device->device_id, $processedExtensions[$extensionNumber]['devices'])) {
                    $processedExtensions[$extensionNumber]['devices'][] = $device->device_id;
                }
                
                // Create device-extension relationship
                DB::table('device_extensions')->updateOrInsert(
                    [
                        'device_id' => $device->device_id,
                        'extension_id' => $extension->extension_id,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
                
                Log::info('Extension linked to device', [
                    'extension' => $extensionNumber,
                    'device_id' => $device->device_id
                ]);
                
            } catch (\Exception $e) {
                Log::error('Error processing registration', [
                    'registration' => $registration,
                    'error' => $e->getMessage()
                ]);
                continue;
            }
        }
        
        // Update devices_registered count for each extension
        foreach ($processedExtensions as $extensionNumber => $data) {
            $extension = $data['extension'];
            $deviceCount = count($data['devices']);
            
            $extension->devices_registered = $deviceCount;
            $extension->save();
            
            Log::info('Updated extension device count', [
                'extension' => $extensionNumber,
                'devices_registered' => $deviceCount
            ]);
        }
    }
}
  