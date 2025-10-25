<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use MongoDB\BSON\UTCDateTime;
use Illuminate\Support\Carbon;

class ETLService
{
    public function run(?string $since = null): array
    {
    //     // 1. Get call records from PostgreSQL
    //     $callRecords = $this->getCallRecordsFromPostgres($since);

    //     // 2. Get devices from MongoDB
    //     $mongoDevices = $this->getDevicesFromMongo($since);
        
    //     Log::info('ETL started', [
    //         'since' => $since,
    //         'postgres_count' => $callRecords->count(),
    //         'mongo_count' => count($mongoDevices)
    //     ]);
        
        
    //     // 3. Combine them
    //     $combined = $this->combineData($callRecords, $mongoDevices);
        
    //     // 4. Save/Update to MariaDB devices table
    //     $this->saveToMariaDB($combined);
        
    //     return [
    //         'call_records_found' => $callRecords->count(),
    //         'mongo_devices_found' => count($mongoDevices),
    //         'devices_synced' => $combined->count(),
    //     ];
    // }

    private function getUserFromPostgres(?string $since = null): Collection
    {
       $query = DB::connection('pgsql')
        ->table('user')
        ->select('first_name', 'last_name','user_name');
        //
        // Only get new/updated records
        if ($since) {
            $timestamp = Carbon::parse($since)->format('Y-m-d H:i:s');
            
        }
        
        return $query->get()->groupBy('first_name');
    }

    private function getregistrationsFromMongo(?string $since = null): array
    {   
        $filter = [];
        if ($since) {
            $timestamp = Carbon::parse($since);
            $filter[] = ['$gte' => new UTCDateTime($timestamp->timestamp * 1000)];
        }
        
        $cursor = DB::connection('mongodb')
            ->getDatabase()
            ->selectCollection('devices')
            ->find($filter);

        $devices = iterator_to_array($cursor);

        // Index by device_id
        $indexed = [];
        foreach ($devices as $device) {
            $deviceId = $device->device_id ?? $device['device_id'] ?? null;
            if ($deviceId) {
                $indexed[$deviceId] = $device;
            }
        }

        return $indexed;
    }

    private function combineData(Collection $callRecordsByDevice, array $mongoDevices): Collection
    {
        $result = collect();

        // Start with MongoDB devices as the base
        foreach ($mongoDevices as $deviceId => $mongoDevice) {
            // Get the latest call for this device from PostgreSQL
            $deviceCalls = $callRecordsByDevice->get($deviceId, collect());
            $latestCall = $deviceCalls->sortByDesc('call_start')->first();

            $result->push([
                'device_id' => $deviceId,
                'owner' => $mongoDevice->owner ?? null,
                'ip_address' => $mongoDevice->ip_address ?? null,
                'last_registered' => isset($mongoDevice->last_registered) 
                    ? date('Y-m-d H:i:s', strtotime($mongoDevice->last_registered))
                    : null,
                'status' => $mongoDevice->status ?? 'unknown',
                'building' => $mongoDevice->building ?? null,
                
                // Add call info from PostgreSQL if available
                'call_started' => $latestCall->call_start ?? null,
                'call_ended' => $latestCall->call_end ?? null,
                'updated_at' => now(),
            ]);
        }

        return $result;
    }

    private function saveToMariaDB(Collection $data): void
    {
        if ($data->isEmpty()) {
            Log::info('No data to sync.');
            return;
        }

        foreach ($data as $device) {
            // Use updateOrInsert for upsert (update if exists, insert if not)
            DB::table('devices')->updateOrInsert(
                ['device_id' => $device['device_id']], // Match condition
                array_merge($device, ['updated_at' => now()]) // Data to update/insert
            );
        }
        Log::info("Synced {$data->count()} devices.");
    }
}