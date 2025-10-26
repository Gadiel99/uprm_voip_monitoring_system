<?php

namespace App\Services;

use App\Models\Devices;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use MongoDB\BSON\UTCDateTime;
use Illuminate\Support\Carbon;

class ETLService
{
    public function run(?string $since = null): array
    {
        // 1. Get users from PostgreSQL
        $users = $this->getUsersFromPostgres();

        // 2. Get registrations from MongoDB
        $mongoRegistrations = $this->getRegistrationsFromMongo($since);
        
        Log::info('ETL started', [
            'since' => $since,
            'postgres_users_count' => $users->count(),
            'mongo_registrations_count' => count($mongoRegistrations)
        ]);
        
        // 3. Mark all existing devices as inactive first
        Devices::query()->update(['status' => 'inactive']);
        
        // 4. Process and save to MariaDB
        $result = $this->processAndSave($users, $mongoRegistrations);
        
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

        // Group registrations by IP address (device)
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

        // Process each device
        foreach ($deviceGroups as $ipAddress => $registrations) {
            $extensionNumbers = []; // Simple array of extension numbers
            $owners = [];
            $macAddress = null;

            foreach ($registrations as $registration) {
                $identity = $registration->identity ?? null;
                if (!$identity) continue;

                // Extract extension number
                $extensionNumber = explode('@', $identity)[0];
                
                // Find matching user
                $user = $users->firstWhere('user_name', $extensionNumber);
                if (!$user) {
                    Log::warning("No user found for extension {$extensionNumber}");
                    continue;
                }

                $owners[] = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
                
                // Get MAC address from first registration
                if (!$macAddress) {
                    $macAddress = $registration->instrument ?? 'unknown';
                }

                // Add just the extension number to array
                $extensionNumbers[] = $extensionNumber;
            }

            if (empty($extensionNumbers)) {
                Log::warning("No valid extensions for device IP {$ipAddress}");
                continue;
            }

            // Create or update device with all extensions
            $device = Devices::updateOrCreate(
                ['ip_address' => $ipAddress],
                [
                    'owner' => implode(', ', array_unique($owners)),
                    'mac_address' => $macAddress,
                    'extension' => $extensionNumbers, // Store as ["4444", "5555"]
                    'status' => 'active',
                    'building_id' => null,
                ]
            );

            if ($device->wasRecentlyCreated) {
                $devicesCreated++;
                Log::info("✅ Created device: IP={$ipAddress}, Extensions=[" . implode(', ', $extensionNumbers) . "]");
            } else {
                $devicesUpdated++;
                Log::info("🔄 Updated device: IP={$ipAddress}, Extensions=[" . implode(', ', $extensionNumbers) . "]");
            }
        }

        $devicesInactive = Devices::where('status', 'inactive')->count();

        Log::info('ETL completed', [
            'devices_created' => $devicesCreated,
            'devices_updated' => $devicesUpdated,
            'devices_active' => count($deviceGroups),
            'devices_inactive' => $devicesInactive,
        ]);

        return [
            'devices_created' => $devicesCreated,
            'devices_updated' => $devicesUpdated,
            'devices_active' => count($deviceGroups),
            'devices_inactive' => $devicesInactive,
        ];
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