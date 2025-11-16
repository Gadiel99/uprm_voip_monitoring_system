<?php

/**
 * @file CleanupImportFiles.php
 * @brief Artisan command to clean up old import files
 * @details Scheduled command to remove old archive and extracted files
 * @author UPRM VoIP Monitoring System Team
 * @date November 16, 2025
 * @version 1.0
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FileCleanupService;

/**
 * @class CleanupImportFiles
 * @brief Command to clean up old ETL import files
 * @details Runs file cleanup service with configurable retention periods
 * @author UPRM VoIP Monitoring System Team
 * @date November 16, 2025
 */
class CleanupImportFiles extends Command
{
    /**
     * @brief The name and signature of the console command.
     * @var string
     */
    protected $signature = 'imports:cleanup
                            {--archive-days= : Number of days to retain archive files (default: 30)}
                            {--extracted-days= : Number of days to retain extracted directories (default: 7)}
                            {--stats : Show storage statistics instead of cleaning}';

    /**
     * @brief The console command description.
     * @var string
     */
    protected $description = 'Clean up old import archive and extracted files based on retention policy';

    /**
     * @brief File cleanup service instance
     * @var FileCleanupService
     */
    private FileCleanupService $cleanupService;

    /**
     * @brief Constructor
     * @param FileCleanupService $cleanupService
     */
    public function __construct(FileCleanupService $cleanupService)
    {
        parent::__construct();
        $this->cleanupService = $cleanupService;
    }

    /**
     * @brief Execute the console command.
     * @details Runs file cleanup or displays storage statistics
     * 
     * @return int Command exit code
     * 
     * @author UPRM VoIP Monitoring System Team
     * @date November 16, 2025
     */
    public function handle(): int
    {
        // If --stats flag is set, show statistics instead of cleaning
        if ($this->option('stats')) {
            return $this->showStats();
        }

        $this->info('Starting import file cleanup...');
        $this->newLine();

        // Get retention options
        $archiveDays = $this->option('archive-days') 
            ? (int)$this->option('archive-days') 
            : null;
            
        $extractedDays = $this->option('extracted-days') 
            ? (int)$this->option('extracted-days') 
            : null;

        // Display retention policy
        $this->line('Retention Policy:');
        $this->line('  Archives: ' . ($archiveDays ?? 30) . ' days');
        $this->line('  Extracted: ' . ($extractedDays ?? 7) . ' days');
        $this->newLine();

        // Run cleanup
        $results = $this->cleanupService->cleanup($archiveDays, $extractedDays);

        // Display results
        $this->line('Cleanup Results:');
        $this->line('  Archives deleted: ' . $results['archives_deleted']);
        $this->line('  Space freed: ' . $this->formatBytes($results['archives_size_freed']));
        $this->line('  Extracted dirs deleted: ' . $results['extracted_deleted']);
        $this->line('  Space freed: ' . $this->formatBytes($results['extracted_size_freed']));

        // Display any errors
        if (!empty($results['errors'])) {
            $this->newLine();
            $this->error('Errors encountered:');
            foreach ($results['errors'] as $error) {
                $this->line('  • ' . $error);
            }
        }

        $this->newLine();
        $this->info('✓ Cleanup completed successfully');

        return Command::SUCCESS;
    }

    /**
     * @brief Show storage statistics
     * @details Displays current storage usage and file counts
     * 
     * @return int Command exit code
     * 
     * @author UPRM VoIP Monitoring System Team
     * @date November 16, 2025
     */
    private function showStats(): int
    {
        $this->info('Import File Storage Statistics');
        $this->newLine();

        $stats = $this->cleanupService->getStorageStats();

        // Archives
        $this->line('Archives (storage/app/imports/archives):');
        $this->line('  Files: ' . $stats['archives']['count']);
        $this->line('  Total Size: ' . $stats['archives']['size_formatted']);
        if ($stats['archives']['oldest']) {
            $this->line('  Oldest: ' . $stats['archives']['oldest']);
            $this->line('  Newest: ' . $stats['archives']['newest']);
        }
        $this->newLine();

        // Extracted
        $this->line('Extracted Directories (storage/app/imports/extracted):');
        $this->line('  Directories: ' . $stats['extracted']['count']);
        $this->line('  Total Size: ' . $stats['extracted']['size_formatted']);
        if ($stats['extracted']['oldest']) {
            $this->line('  Oldest: ' . $stats['extracted']['oldest']);
            $this->line('  Newest: ' . $stats['extracted']['newest']);
        }

        return Command::SUCCESS;
    }

    /**
     * @brief Format bytes into human-readable string
     * @param int $bytes
     * @return string
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
