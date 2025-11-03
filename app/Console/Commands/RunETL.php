<?php

/**
 * @file RunETL.php
 * @brief Command class for executing the ETL (Extract, Transform, Load) pipeline
 * @details This file implements the ETL command that processes data from imported files
 *          and loads it into MariaDB for the UPRM VoIP Monitoring System
 * @author UPRM VoIP Monitoring System Team
 * @date November 2, 2025
 * @version 3.0
 */

namespace App\Console\Commands;

use App\Services\ETLService;
use Illuminate\Console\Command;

/**
 * @class RunETL
 * @brief Laravel Artisan command for running the ETL pipeline
 * @details This class provides a command-line interface for executing the ETL process
 *          that extracts data from imported files (CSV and JSON), transforms it, 
 *          and loads it into MariaDB for the VoIP monitoring system
 * @extends Illuminate\Console\Command
 * @author UPRM VoIP Monitoring System Team
 * @date November 2, 2025
 */

class RunETL extends Command
{
  
    protected $signature = 'etl:run {--import= : Path to extracted import directory (required)}';
    protected $description = 'Run ETL: Import Files → Transform → Load to MariaDB';

    /**
     * @brief Main command handler method
     * @details Executes the ETL pipeline process by coordinating with ETLService.
     *          Supports two modes:
     *          1. Import Mode: Processes data from extracted import files
     *          2. Live Mode: Processes data directly from PostgreSQL and MongoDB
     *          
     *          The method performs the following operations:
     *          1. Determines execution mode (import or live)
     *          2. Validates command-line options
     *          3. Executes the ETL pipeline through ETLService
     *          4. Displays execution statistics in formatted table
     *          5. Handles exceptions and provides error reporting
     * 
     * @param ETLService $etl The ETL service instance injected by Laravel's container
     * @return int Command exit code (SUCCESS=0 or FAILURE=1)
     * 
     * @throws \Exception When ETL process encounters unrecoverable errors
     * 
     * @see App\Services\ETLService::run()
     * @author UPRM VoIP Monitoring System Team
     * @date November 2, 2025
     */
    public function handle(ETLService $etl): int
    {
        // Retrieve command options
        $since = $this->option('since');
        $importPath = $this->option('import');

        // Determine execution mode
        $mode = $importPath ? 'IMPORT' : 'LIVE';
        
        // Display process initiation message
        $this->info('🚀 Starting ETL process...');
        $this->newLine();
        
        // Display mode-specific information
        if ($importPath) {
            $this->info("📂 Mode: Import from extracted files");
            $this->info("📁 Import Path: {$importPath}");
            
            // Validate import path exists
            if (!is_dir($importPath)) {
                $this->error("❌ Import directory not found: {$importPath}");
                $this->newLine();
                $this->comment('💡 Tip: Run data:import first to extract the archive.');
                return self::FAILURE;
            }
            
            // Warn if --since is provided with --import (it will be ignored)
            if ($since) {
                $this->warn("⚠️  Note: --since option is ignored in import mode");
            }
        } else {
            $this->info("🔗 Mode: Live database connections");
            
            if ($since) {
                $this->info("📅 Processing data since: {$since}");
            } else {
                $this->info("📅 Processing all available data");
            }
        }
        
        $this->newLine();
        
        /*
         * Error handling wrapper for the entire ETL process
         * Catches any exceptions thrown during ETL execution and provides
         * appropriate error reporting and exit codes
         */
        try {
            /*
             * Execute the main ETL pipeline process
             * The ETLService::run() method handles:
             * - Data extraction from files or databases
             * - Data transformation and validation
             * - Data loading into MariaDB
             * - Statistics collection and reporting
             */
            if ($importPath) {
                // Import mode: process from files
                $this->line("📊 Extracting data from import files...");
                $stats = $etl->run('null', $importPath);
            } else {
                // Live mode: process from databases
                $this->line("📊 Extracting data from live databases...");
                $sinceCarbon = $since ? \Carbon\Carbon::parse($since) : null;
                $stats = $etl->run($sinceCarbon, 'null');
            }

            $this->newLine();
            $this->info('✅ ETL process completed successfully!');
            $this->newLine();
            
            /*
             * Display execution statistics in formatted table
             * Shows counts for created/updated devices and extensions,
             * as well as current online/offline device status
             */
            $this->info('📈 Processing Summary:');
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Devices Created', number_format($stats['devices_created'] ?? 0)],
                    ['Devices Updated', number_format($stats['devices_updated'] ?? 0)],
                    ['Extensions Created', number_format($stats['extensions_created'] ?? 0)],
                    ['Extensions Updated', number_format($stats['extensions_updated'] ?? 0)],
                    ['─────────────────', '──────'],
                    ['Devices Online', number_format($stats['devices_online'] ?? 0)],
                    ['Devices Offline', number_format($stats['devices_offline'] ?? 0)],
                ]
            );

            $this->newLine();
            
            // Display success message based on mode
            if ($importPath) {
                $this->comment("✨ Data from imported files has been successfully processed into MariaDB");
            } else {
                $this->comment("✨ Data from live databases has been successfully synchronized to MariaDB");
            }
            
            // Return success exit code
            return self::SUCCESS;

        } catch (\Exception $e) {
            /*
             * Exception handling block
             * Catches any exceptions during ETL process execution,
             * displays error messages with stack trace for debugging,
             * and returns failure exit code
             */
            $this->newLine();
            $this->error('❌ ETL process failed: ' . $e->getMessage());
            $this->newLine();
            
            // Show stack trace in verbose mode
            if ($this->output->isVerbose()) {
                $this->error($e->getTraceAsString());
                $this->newLine();
            } else {
                $this->comment('💡 Run with -v for detailed error trace');
            }
            
            // Provide helpful hints based on error message
            if (strpos($e->getMessage(), 'not found') !== false) {
                $this->newLine();
                $this->comment('💡 Troubleshooting:');
                if ($importPath) {
                    $this->line('   • Verify the import directory path is correct');
                    $this->line('   • Ensure users.csv and registrar.json exist in the import directory');
                    $this->line('   • Run: php artisan data:import --list to see available imports');
                } else {
                    $this->line('   • Check database connection settings in .env file');
                    $this->line('   • Verify PostgreSQL and MongoDB services are running');
                    $this->line('   • Test connections: php artisan db:show');
                }
            }
            
            return self::FAILURE;
        }
    }
}