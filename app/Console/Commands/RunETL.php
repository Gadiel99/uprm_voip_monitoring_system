<?php
// filepath: c:\Users\jay20\Documents\Universidad\Capstone\Herd\uprm_voip_monitoring_system\app\Console\Commands\RunETL.php

namespace App\Console\Commands;

use App\Services\ETLService;
use Illuminate\Console\Command;

class RunETL extends Command
{
    protected $signature = 'etl:run {--since= : Only process data since this time (e.g., "5 minutes ago")}';
    protected $description = 'Run ETL: Postgres + Mongo → MariaDB';

    public function handle(ETLService $etl): int
    {

        $since = $this->option('since');

        $this->info('🚀 Starting ETL process...');

        if ($since) {
            $this->info("📅 Processing data since: {$since}");
        }
        
        try {
            $stats = $etl->run($since);

            $this->info('✅ ETL process completed successfully.');
            
            // Show the stats table
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Call Records Found (Postgres)', $stats['call_records_found'] ?? 0],
                    ['Devices Found (Mongo)', $stats['mongo_devices_found'] ?? 0],
                    ['Devices Synced (MariaDB)', $stats['devices_synced'] ?? 0],
                ]
            );
            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ ETL process failed: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return self::FAILURE;
        }
    }
}