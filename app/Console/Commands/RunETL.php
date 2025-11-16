<?php

namespace App\Console\Commands;

use App\Services\ETLService;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class RunETL extends Command
{
    /**
     * @brief Command signature defining the command name and options
     * @details Defines the artisan command signature "etl:run" with required import path
     * @var string $signature The command signature string
     */
    protected $signature = 'etl:run 
                            {--import= : Path to extracted import directory (required)}';
    
    /**
     * @brief Brief description of the command functionality
     * @details Human-readable description shown in artisan command list
     * @var string $description Command description string
     */
    protected $description = 'Run ETL: Import Files → Transform → Load to MariaDB';

    /**
     * @brief Main command handler method
     * @details Executes the ETL pipeline process by coordinating with ETLService.
     *          Extracts data from imported files, transforms, and loads into MariaDB
     *          
     *          The method performs the following operations:
     *          1. Validates import path is provided
     *          2. Validates import directory exists
     *          3. Executes the ETL pipeline through ETLService
     *          4. Displays execution statistics in formatted table
     *          5. Checks and sends notifications for critical conditions
     *          6. Handles exceptions and provides error reporting
     * 
     * @param ETLService $etl The ETL service instance injected by Laravel's container
     * @param NotificationService $notificationService The notification service instance
     * @return int Command exit code (SUCCESS=0 or FAILURE=1)
     * 
     * @throws \Exception When ETL process encounters unrecoverable errors
     * 
     * @see App\Services\ETLService::run()
     * @author UPRM VoIP Monitoring System Team
     * @date November 6, 2025
     */
    public function handle(ETLService $etl, NotificationService $notificationService): int
    {
        // Get import path
        $importPath = $this->option('import');
        
        // Display process initiation message
        $this->info('🚀 Starting ETL process...');
        $this->newLine();
        
        // Validate import path is provided
        if (!$importPath) {
            $this->error("❌ The --import option is required");
            $this->newLine();
            $this->comment('💡 Usage:');
            $this->line('   php artisan etl:run --import=/path/to/extracted/import');
            $this->newLine();
            $this->comment('💡 First, extract the archive using:');
            $this->line('   php artisan data:import /path/to/archive.tar.gz');
            return self::FAILURE;
        }
        
        // Display import information
        $this->info("� Mode: Import from extracted files");
        $this->info("📁 Import Path: {$importPath}");
        
        // Validate import path exists
        if (!is_dir($importPath)) {
            $this->error("❌ Import directory not found: {$importPath}");
            $this->newLine();
            $this->comment('💡 Tip: Run data:import first to extract the archive.');
            $this->line('   php artisan data:import /path/to/archive.tar.gz');
            $this->newLine();
            $this->comment('💡 To see available imports:');
            $this->line('   php artisan data:import --list');
            return self::FAILURE;
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
             * - Data extraction from imported files
             * - Data transformation and validation
             * - Data loading into MariaDB
             * - Statistics collection and reporting
             */
            $this->line("📊 Extracting data from import files...");
            $stats = $etl->run($importPath);

            $this->newLine();
            $this->info('✅ ETL process completed successfully!');
            $this->newLine();
            
            /*
             * Display formatted statistics table
             * Shows metrics collected during the ETL process
             */
            $this->line('📈 Processing Summary:');
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Devices Created', $stats['devices_created']],
                    ['Devices Updated', $stats['devices_updated']],
                    ['Extensions Created', $stats['extensions_created']],
                    ['Extensions Updated', $stats['extensions_updated']],
                    ['─────────────────', '──────'],
                    ['Devices Online', $stats['devices_online']],
                    ['Devices Offline', $stats['devices_offline']],
                ]
            );

            $this->newLine();
            $this->line('✨ Data from imported files has been successfully processed into MariaDB');
            $this->newLine();

            // After ETL completes, run consolidated notification using real data
            $this->info('📧 Running notifications:test (consolidated email with real data)...');
            $exit = Artisan::call('notifications:test');
            $this->line(rtrim(Artisan::output()));
            $this->info($exit === 0 ? '✓ Notifications:test completed' : '⚠ Notifications:test exited with non-zero code');
            $this->newLine();

            return self::SUCCESS;

        } catch (\Exception $e) {
            /*
             * Exception handling and error reporting
             * Provides detailed error information for troubleshooting
             */
            $this->newLine();
            $this->error('❌ ETL process failed!');
            $this->newLine();
            $this->error('Error: ' . $e->getMessage());
            $this->newLine();
            $this->comment('Stack trace:');
            $this->line($e->getTraceAsString());
            $this->newLine();
            
            return self::FAILURE;
        }
    }
}