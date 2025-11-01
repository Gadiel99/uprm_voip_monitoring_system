<?php

/**
 * @file RunETL.php
 * @brief Command class for executing the ETL (Extract, Transform, Load) pipeline
 * @details This file implements the ETL command that processes data from PostgreSQL 
 *          and MongoDB sources and loads it into MariaDB for the UPRM VoIP Monitoring System
 * @author UPRM VoIP Monitoring System Team
 * @date October 30, 2025
 * @version 1.0
 */

namespace App\Console\Commands;

use App\Services\ETLService;
use Illuminate\Console\Command;

/**
 * @class RunETL
 * @brief Laravel Artisan command for running the ETL pipeline
 * @details This class provides a command-line interface for executing the ETL process
 *          that extracts data from PostgreSQL and MongoDB, transforms it, and loads
 *          it into MariaDB for the VoIP monitoring system
 * @extends Illuminate\Console\Command
 * @author Gadiel J. De Jesus Martinez - Triatek
 * @date October 30, 2025
 */

class RunETL extends Command
{
    /**
     * @brief Command signature defining the command name and options
     * @details Defines the artisan command signature "etl:run" with an optional 
     *          --since parameter for incremental processing
     * @var string $signature The command signature string
     */
    protected $signature = 'etl:run {--since= : Only process data since this time (e.g., "5 minutes ago")}';
    
    /**
     * @brief Brief description of the command functionality
     * @details Human-readable description shown in artisan command list
     * @var string $description Command description string
     */
    protected $description = 'Run ETL: Postgres + Mongo → MariaDB';

    
    /**
     * @brief Main command handler method
     * @details Executes the ETL pipeline process by coordinating with ETLService.
     *          Handles command-line options, error handling, and output formatting.
     *          The method performs the following operations:
     *          1. Parses command-line options (--since parameter)
     *          2. Initializes the ETL process with appropriate parameters
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
     * @author Gadiel J. De Jesus Martinez - Triatek
     * @date October 30, 2025
     */
    public function handle(ETLService $etl): int
    {
        // Extract the --since option value from command-line arguments
        $since = $this->option('since');

        // Display process initiation message to user
        $this->info('🚀 Starting ETL process...');

        // Display incremental processing information if --since option provided
        if ($since) {
            $this->info("📅 Processing data since: {$since}");
        }
        
        /*
         * Error handling wrapper for the entire ETL process
         * Catches any exceptions thrown during ETL execution and provides
         * appropriate error reporting and exit codes
         */
        try {
            /*
             * Execute the main ETL pipeline process
             * The ETLService::run() method handles:
             * - Data extraction from PostgreSQL and MongoDB
             * - Data transformation and validation
             * - Data loading into MariaDB
             * - Statistics collection and reporting
             */
            $stats = $etl->run($since);

            // Display successful completion message
            $this->info('✅ ETL process completed successfully.');
            
            /*
             * Display execution statistics in formatted table
             * Shows counts for created/updated devices and extensions,
             * as well as current online/offline device status
             */
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

            // Return success exit code
            return self::SUCCESS;

        } catch (\Exception $e) {
            /*
             * Exception handling block
             * Catches any exceptions during ETL process execution,
             * displays error messages with stack trace for debugging,
             * and returns failure exit code
             */
            $this->error('❌ ETL process failed: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return self::FAILURE;
        }
    }
}