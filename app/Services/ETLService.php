<?php

namespace App\Services;

use App\Models\Devices;
use App\Models\Extensions;
use App\Models\Buildings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use MongoDB\BSON\UTCDateTime;
use Illuminate\Support\Carbon;

class ETLService
{
    public function run(?string $since = null): array
    {
        $users = $this->getUsersFromPostgres();
        $mongoRegistrations = $this->getRegistrationsFromMongo($since);
        
        Log::info('ETL started', [
            'postgres_users_count' => $users->count(),
            'mongo_registrations_count' => count($mongoRegistrations)
        ]);
        
        // IMPORTANT: Mark all devices as offline first
        Devices::query()->update(['status' => 'offline']);
        
        $result = $this->processAndSave($users, $mongoRegistrations);
        
        // Update building statistics
        $this->updateBuildingStats();
        
        return $result;
    }

    private function getUsersFromPostgres(): Collection
    {
        return DB::connection('pgsql')
            ->table('users')
            ->select('first_name', 'last_name', 'user_name')
            ->get();
    }

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

    private function processAndSave(Collection $users, array $mongoRegistrations): array
    {
        $devicesCreated = 0;
        $devicesUpdated = 0;
        $extensionsCreated = 0;
        $extensionsUpdated = 0;

        // Group registrations by IP ADDRESS
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

        // Track extension device counts
        $extensionDeviceCount = [];

        // Process each device that appears in registrar
        foreach ($deviceGroups as $ipAddress => $registrations) {
            $extensionNumbers = [];
            $macAddress = null;

            foreach ($registrations as $registration) {
                $identity = $registration->identity ?? null;
                if (!$identity) continue;

                $extensionNumber = explode('@', $identity)[0];
                $user = $users->firstWhere('user_name', $extensionNumber);
                
                if (!$user) {
                    Log::warning("No user found for extension {$extensionNumber}");
                    continue;
                }

                // Add to device's extension list
                if (!in_array($extensionNumber, $extensionNumbers)) {
                    $extensionNumbers[] = $extensionNumber;
                }

                // Track device count for this extension
                if (!isset($extensionDeviceCount[$extensionNumber])) {
                    $extensionDeviceCount[$extensionNumber] = [
                        'count' => 0,
                        'user' => $user
                    ];
                }
                $extensionDeviceCount[$extensionNumber]['count']++;

                // Get MAC address
                if (!$macAddress) {
                    $macAddress = $registration->instrument ?? 'unknown';
                }
            }

            if (empty($extensionNumbers)) {
                Log::warning("No valid extensions for device IP {$ipAddress}");
                continue;
            }

            // Create or update device - Set status to ONLINE because it's in registrar
            $device = Devices::updateOrCreate(
                ['ip_address' => $ipAddress],
                [
                    'mac_address' => $macAddress,
                    'extensions' => $extensionNumbers,
                    'status' => 'online', // Device is ONLINE if it appears in registrar
                    'building_id' => null,
                ]
            );

            if ($device->wasRecentlyCreated) {
                $devicesCreated++;
                Log::info("✅ Created device: IP={$ipAddress}, Status=ONLINE, Extensions=[" . implode(', ', $extensionNumbers) . "]");
            } else {
                $devicesUpdated++;
                Log::info("🔄 Updated device: IP={$ipAddress}, Status=ONLINE, Extensions=[" . implode(', ', $extensionNumbers) . "]");
            }
        }

        // Update extensions table
        foreach ($extensionDeviceCount as $extensionNumber => $data) {
            $user = $data['user'];
            $deviceCount = $data['count'];

            $extension = Extensions::updateOrCreate(
                ['extension_number' => $extensionNumber],
                [
                    'user_first_name' => $user->first_name,
                    'user_last_name' => $user->last_name,
                    'devices_registered' => $deviceCount,
                ]
            );

            if ($extension->wasRecentlyCreated) {
                $extensionsCreated++;
                Log::info("✅ Created extension: {$extensionNumber} ({$deviceCount} devices)");
            } else {
                $extensionsUpdated++;
                Log::info("🔄 Updated extension: {$extensionNumber} ({$deviceCount} devices)");
            }
        }

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

    private function updateBuildingStats()
    {
        $buildings = Buildings::all();
        
        foreach ($buildings as $building) {
            $building->updateDeviceCounts();
            Log::info("Updated building stats: {$building->building_name} - Total: {$building->total_devices}, Offline: {$building->offline_devices}");
        }
    }

    private function extractIPFromBinding(?string $binding): ?string
    {
        if (!$binding) return null;
        
        if (preg_match('/@(\d+\.\d+\.\d+\.\d+)/', $binding, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
}