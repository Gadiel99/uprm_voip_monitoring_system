<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DeviceActivityService;

/**
 * Command to manually record device activity for the current 5-minute interval
 */
class RecordDeviceActivity extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'activity:record';

    /**
     * The console command description.
     */
    protected $description = 'Record device activity for the current 5-minute interval';

    /**
     * Device activity service
     */
    private DeviceActivityService $activityService;

    /**
     * Constructor
     */
    public function __construct(DeviceActivityService $activityService)
    {
        parent::__construct();
        $this->activityService = $activityService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $this->info('Recording device activity...');
            
            $this->activityService->recordActivity();
            
            $this->info('✓ Device activity recorded successfully');
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Failed to record device activity: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
