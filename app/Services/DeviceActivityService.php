<?php

namespace App\Services;

use App\Models\Devices;
use App\Models\DeviceActivity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DeviceActivityService
{
    /**
     * Record device activity for the current 5-minute interval
     * Should be called every time the ETL runs (every 5 minutes)
     */
    public function recordActivity(): void
    {
        $now = Carbon::now();
        $today = $now->toDateString();
        
        // Calculate which sample index this is (0-287)
        // 288 samples per day = every 5 minutes
        $minutesSinceMidnight = ($now->hour * 60) + $now->minute;
        $sampleIndex = floor($minutesSinceMidnight / 5);
        
        // Ensure we don't exceed 287 (0-based index for 288 samples)
        if ($sampleIndex > 287) {
            $sampleIndex = 287;
        }
        
        Log::info('Recording device activity', [
            'date' => $today,
            'time' => $now->format('H:i:s'),
            'sample_index' => $sampleIndex,
        ]);
        
        // Get all devices
        $devices = Devices::all();
        
        foreach ($devices as $device) {
            // Get or create today's activity record (day_number = 1)
            $activity = DeviceActivity::firstOrCreate(
                [
                    'device_id' => $device->device_id,
                    'day_number' => 1,
                ],
                [
                    'activity_date' => $today,
                    'samples' => array_fill(0, 288, 0),
                ]
            );
            
            // Always update activity_date to ensure it's current
            $activity->activity_date = $today;
            
            // Record actual device status: 1 = online, 0 = offline
            $samples = $activity->samples;
            $samples[$sampleIndex] = ($device->status === 'online') ? 1 : 0;
            $activity->samples = $samples;
            $activity->updated_at = $now;
            $activity->save();
        }
        
        Log::info('Device activity recorded', [
            'devices_processed' => $devices->count(),
            'sample_index' => $sampleIndex,
        ]);
    }
    
    /**
     * Rotate activity data at midnight
     * Move day 1 to day 2, delete old day 2, create new day 1
     */
    public function rotateActivityData(): void
    {
        $today = Carbon::now()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();
        
        Log::info('Starting activity data rotation', [
            'today' => $today,
            'yesterday' => $yesterday,
        ]);
        
        DB::beginTransaction();
        
        try {
            // Step 1: Delete all day_number = 2 records (older than yesterday)
            $deleted = DeviceActivity::where('day_number', 2)->delete();
            Log::info('Deleted old day 2 records', ['count' => $deleted]);
            
            // Step 2: Update activity_date for day 1 records before moving to day 2
            DeviceActivity::where('day_number', 1)
                ->update(['activity_date' => $yesterday]);
            Log::info('Updated activity_date for day 1 records');
            
            // Step 3: Move day_number = 1 to day_number = 2
            $updated = DeviceActivity::where('day_number', 1)
                ->update([
                    'day_number' => 2,
                    'updated_at' => now(),
                ]);
            Log::info('Moved day 1 to day 2', ['count' => $updated]);
            
            // Step 4: Create new day_number = 1 records for all devices
            $devices = Devices::all();
            foreach ($devices as $device) {
                DeviceActivity::create([
                    'device_id' => $device->device_id,
                    'activity_date' => $today,
                    'day_number' => 1,
                    'samples' => array_fill(0, 288, 0),
                ]);
            }
            Log::info('Created new day 1 records', ['count' => $devices->count()]);
            
            DB::commit();
            
            Log::info('Activity data rotation completed successfully');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Activity data rotation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    
    /**
     * Get activity data for a device
     * 
     * @param int $deviceId
     * @param int $dayNumber 1 = today, 2 = yesterday
     * @return array|null Array of 288 samples or null if not found
     */
    public function getDeviceActivity(int $deviceId, int $dayNumber = 1): ?array
    {
        $activity = DeviceActivity::where('device_id', $deviceId)
            ->where('day_number', $dayNumber)
            ->first();
        
        if (!$activity) {
            return null;
        }
        
        return [
            'activity_date' => $activity->activity_date->format('Y-m-d'),
            'day_number' => $activity->day_number,
            'samples' => $activity->samples,
        ];
    }
    
    /**
     * Initialize activity tracking for a new device
     */
    public function initializeDeviceActivity(int $deviceId): void
    {
        $today = Carbon::now()->toDateString();
        
        // Create day 1 record with all zeros
        DeviceActivity::firstOrCreate(
            [
                'device_id' => $deviceId,
                'day_number' => 1,
            ],
            [
                'activity_date' => $today,
                'samples' => array_fill(0, 288, 0),
            ]
        );
        
        Log::info('Initialized activity tracking for device', ['device_id' => $deviceId]);
    }
}
