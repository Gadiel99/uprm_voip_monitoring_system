<?php

/**
 * @file DatabaseBackup.php
 * @brief Artisan command for creating database backups
 * @details Scheduled command to create weekly MariaDB backups
 * @author UPRM VoIP Monitoring System Team
 * @date November 17, 2025
 * @version 1.0
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BackupService;

/**
 * @class DatabaseBackup
 * @brief Command to create database backups
 * @details Can be run manually or scheduled for weekly execution
 * @author UPRM VoIP Monitoring System Team
 * @date November 17, 2025
 */
class DatabaseBackup extends Command
{
    /**
     * @brief The name and signature of the console command.
     * @var string
     */
    protected $signature = 'backup:database
                            {--list : List all available backups}
                            {--stats : Show backup statistics}';

    /**
     * @brief The console command description.
     * @var string
     */
    protected $description = 'Create a database backup (ZIP format) or list existing backups';

    /**
     * @brief Backup service instance
     * @var BackupService
     */
    private BackupService $backupService;

    /**
     * @brief Constructor
     * @param BackupService $backupService
     */
    public function __construct(BackupService $backupService)
    {
        parent::__construct();
        $this->backupService = $backupService;
    }

    /**
     * @brief Execute the console command.
     * @details Creates backup or shows backup information
     * 
     * @return int Command exit code
     * 
     * @author UPRM VoIP Monitoring System Team
     * @date November 17, 2025
     */
    public function handle(): int
    {
        // Show backup statistics
        if ($this->option('stats')) {
            return $this->showStats();
        }

        // List available backups
        if ($this->option('list')) {
            return $this->listBackups();
        }

        // Create new backup
        $this->info('Creating database backup...');
        $this->newLine();

        $result = $this->backupService->createBackup();

        if ($result['success']) {
            $this->info('✓ Backup created successfully');
            $this->line('  File: ' . $result['file']);
            $this->line('  Size: ' . $result['size']);
            $this->newLine();
            
            return Command::SUCCESS;
        } else {
            $this->error('✗ Backup failed');
            $this->line('  ' . $result['message']);
            $this->newLine();
            
            return Command::FAILURE;
        }
    }

    /**
     * @brief List all available backups
     * @return int Command exit code
     */
    private function listBackups(): int
    {
        $backups = $this->backupService->getAllBackups();

        if (empty($backups)) {
            $this->warn('No backups found');
            return Command::SUCCESS;
        }

        $this->info('Available Backups:');
        $this->newLine();

        $this->table(
            ['Filename', 'Size', 'Created', 'Age'],
            array_map(function($backup) {
                return [
                    $backup['filename'],
                    $backup['size_formatted'],
                    $backup['created_at'],
                    $backup['age']
                ];
            }, $backups)
        );

        return Command::SUCCESS;
    }

    /**
     * @brief Show backup statistics
     * @return int Command exit code
     */
    private function showStats(): int
    {
        $stats = $this->backupService->getBackupStats();

        $this->info('Backup Statistics:');
        $this->newLine();
        $this->line('  Total Backups: ' . $stats['total_backups']);
        $this->line('  Total Size: ' . $stats['total_size_formatted']);
        $this->line('  Retention: ' . $stats['retention_weeks'] . ' weeks');
        
        if ($stats['oldest']) {
            $this->line('  Oldest: ' . $stats['oldest']);
        }
        if ($stats['newest']) {
            $this->line('  Newest: ' . $stats['newest']);
        }
        
        $this->newLine();

        return Command::SUCCESS;
    }
}
