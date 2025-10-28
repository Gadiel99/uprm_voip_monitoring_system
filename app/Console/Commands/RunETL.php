<?php

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
                    ['Devices Created', $stats['devices_created'] ?? 0],
                    ['Devices Updated', $stats['devices_updated'] ?? 0],
                    ['Extensions Created', $stats['extensions_created'] ?? 0],
                    ['Extensions Updated', $stats['extensions_updated'] ?? 0],
                    ['Devices Online', $stats['devices_online'] ?? 0],
                    ['Devices Offline', $stats['devices_offline'] ?? 0],
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