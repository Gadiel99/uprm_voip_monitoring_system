<?php

namespace App\Console\Commands;

use App\Services\DeviceActivityService;
use Illuminate\Console\Command;

class RotateActivityData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activity:rotate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rotate device activity data (move day 1 to day 2, delete old day 2, create new day 1)';

    /**
     * Execute the console command.
     */
    public function handle(DeviceActivityService $activityService): int
    {
        $this->info('🔄 Starting activity data rotation...');
        
        try {
            $activityService->rotateActivityData();
            
            $this->info('✅ Activity data rotation completed successfully!');
            $this->line('   - Old day 2 records deleted');
            $this->line('   - Day 1 moved to day 2');
            $this->line('   - New day 1 records created');
            
            return self::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('❌ Activity data rotation failed!');
            $this->error('Error: ' . $e->getMessage());
            
            return self::FAILURE;
        }
    }
}
