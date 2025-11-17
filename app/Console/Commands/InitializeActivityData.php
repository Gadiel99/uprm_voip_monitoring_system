<?php

namespace App\Console\Commands;

use App\Models\Devices;
use App\Models\DeviceActivity;
use Carbon\Carbon;
use Illuminate\Console\Command;

class InitializeActivityData extends Command
{
    protected $signature = 'activity:initialize';
    protected $description = 'Initialize activity data for all existing devices';

    public function handle(): int
    {
        $this->info('🔧 Initializing activity data for all devices...');
        
        $today = Carbon::now()->toDateString();
        $devices = Devices::all();
        
        if ($devices->isEmpty()) {
            $this->warn('⚠️  No devices found in database');
            return self::SUCCESS;
        }
        
        $this->info("📊 Found {$devices->count()} devices");
        
        $created = 0;
        $skipped = 0;
        
        foreach ($devices as $device) {
            // Check if activity record already exists
            $exists = DeviceActivity::where('device_id', $device->device_id)
                ->where('day_number', 1)
                ->exists();
            
            if ($exists) {
                $skipped++;
                continue;
            }
            
            // Create today's activity record with all zeros
            DeviceActivity::create([
                'device_id' => $device->device_id,
                'activity_date' => $today,
                'day_number' => 1,
                'samples' => array_fill(0, 288, 0),
            ]);
            
            $created++;
        }
        
        $this->newLine();
        $this->info("✅ Initialization complete!");
        $this->line("   - Created: {$created} records");
        $this->line("   - Skipped: {$skipped} records (already exist)");
        $this->newLine();
        $this->line("💡 Activity will be recorded starting with the next ETL run");
        
        return self::SUCCESS;
    }
}
