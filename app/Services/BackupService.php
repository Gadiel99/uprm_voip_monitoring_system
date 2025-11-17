<?php

/**
 * @file BackupService.php
 * @brief Service class for database backup and restore operations
 * @details Handles MariaDB database backups, compression, and restoration
 * @author UPRM VoIP Monitoring System Team
 * @date November 17, 2025
 * @version 1.0
 */

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Exception;
use ZipArchive;

/**
 * @class BackupService
 * @brief Service for creating and managing database backups
 * @details Creates weekly MariaDB dumps, compresses them, and manages retention
 * @author UPRM VoIP Monitoring System Team
 * @date November 17, 2025
 */
class BackupService
{
    /**
     * @brief Backup storage directory
     * @var string
     */
    private const BACKUP_PATH = '/var/backups/monitoring';

    /**
     * @brief Number of weeks to retain backups
     * @var int
     */
    private const RETENTION_WEEKS = 4; // Keep 4 weeks (1 month)

    /**
     * @brief Create a new database backup
     * @details Dumps MariaDB database, compresses it to ZIP, and stores with timestamp
     * 
     * @return array ['success' => bool, 'message' => string, 'file' => string|null]
     * 
     * @author UPRM VoIP Monitoring System Team
     * @date November 17, 2025
     */
    public function createBackup(): array
    {
        try {
            // Create backup directory if it doesn't exist
            $backupDir = self::BACKUP_PATH;
            if (!File::isDirectory($backupDir)) {
                File::makeDirectory($backupDir, 0755, true);
            }

            // Generate backup filename with timestamp
            $timestamp = Carbon::now()->format('Y-m-d_His');
            $databaseName = config('database.connections.mysql.database');
            $sqlFile = $backupDir . "/backup_{$timestamp}.sql";
            $zipFile = $backupDir . "/backup_{$timestamp}.zip";

            // Get database credentials
            $host = config('database.connections.mysql.host');
            $port = config('database.connections.mysql.port', 3306);
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');

            // Build mysqldump command
            $dumpCommand = sprintf(
                'mysqldump --host=%s --port=%s --user=%s --password=%s --single-transaction --quick --lock-tables=false %s > %s 2>&1',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($databaseName),
                escapeshellarg($sqlFile)
            );

            // Execute dump
            exec($dumpCommand, $output, $returnCode);

            if ($returnCode !== 0 || !File::exists($sqlFile)) {
                throw new Exception('Database dump failed: ' . implode("\n", $output));
            }

            // Verify SQL file has content
            if (filesize($sqlFile) < 100) {
                throw new Exception('Database dump file is too small or empty');
            }

            // Compress to ZIP
            $zip = new ZipArchive();
            if ($zip->open($zipFile, ZipArchive::CREATE) !== true) {
                throw new Exception('Failed to create ZIP file');
            }

            $zip->addFile($sqlFile, basename($sqlFile));
            $zip->close();

            // Remove uncompressed SQL file
            File::delete($sqlFile);

            // Clean old backups
            $this->cleanOldBackups();

            Log::info('Database backup created successfully', [
                'file' => basename($zipFile),
                'size' => $this->formatBytes(filesize($zipFile))
            ]);

            return [
                'success' => true,
                'message' => 'Backup created successfully',
                'file' => basename($zipFile),
                'size' => $this->formatBytes(filesize($zipFile)),
                'path' => $zipFile
            ];

        } catch (Exception $e) {
            Log::error('Backup creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Backup failed: ' . $e->getMessage(),
                'file' => null
            ];
        }
    }

    /**
     * @brief Get the most recent backup file
     * @details Returns info about the latest backup ZIP file
     * 
     * @return array|null Backup info or null if no backups exist
     * 
     * @author UPRM VoIP Monitoring System Team
     * @date November 17, 2025
     */
    public function getLatestBackup(): ?array
    {
        $backupDir = self::BACKUP_PATH;
        
        if (!File::isDirectory($backupDir)) {
            return null;
        }

        $files = File::glob($backupDir . '/backup_*.zip');
        
        if (empty($files)) {
            return null;
        }

        // Sort by modification time (newest first)
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        $latestFile = $files[0];
        $filename = basename($latestFile);
        $timestamp = $this->extractTimestampFromFilename($filename);

        return [
            'filename' => $filename,
            'path' => $latestFile,
            'size' => filesize($latestFile),
            'size_formatted' => $this->formatBytes(filesize($latestFile)),
            'created_at' => $timestamp ? $timestamp->format('Y-m-d H:i:s') : Carbon::createFromTimestamp(filemtime($latestFile))->format('Y-m-d H:i:s'),
            'age' => $timestamp ? $timestamp->diffForHumans() : Carbon::createFromTimestamp(filemtime($latestFile))->diffForHumans()
        ];
    }

    /**
     * @brief Get all backup files
     * @details Returns list of all available backups sorted by date
     * 
     * @return array List of backup info arrays
     * 
     * @author UPRM VoIP Monitoring System Team
     * @date November 17, 2025
     */
    public function getAllBackups(): array
    {
        $backupDir = self::BACKUP_PATH;
        
        if (!File::isDirectory($backupDir)) {
            return [];
        }

        $files = File::glob($backupDir . '/backup_*.zip');
        
        if (empty($files)) {
            return [];
        }

        // Sort by modification time (newest first)
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        return array_map(function($file) {
            $filename = basename($file);
            $timestamp = $this->extractTimestampFromFilename($filename);
            
            return [
                'filename' => $filename,
                'path' => $file,
                'size' => filesize($file),
                'size_formatted' => $this->formatBytes(filesize($file)),
                'created_at' => $timestamp ? $timestamp->format('Y-m-d H:i:s') : Carbon::createFromTimestamp(filemtime($file))->format('Y-m-d H:i:s'),
                'age' => $timestamp ? $timestamp->diffForHumans() : Carbon::createFromTimestamp(filemtime($file))->diffForHumans()
            ];
        }, $files);
    }

    /**
     * @brief Restore database from a backup file
     * @details Extracts ZIP, imports SQL dump into MariaDB
     * 
     * @param string $filename Backup filename to restore
     * @return array ['success' => bool, 'message' => string]
     * 
     * @author UPRM VoIP Monitoring System Team
     * @date November 17, 2025
     */
    public function restoreBackup(string $filename): array
    {
        try {
            $backupDir = self::BACKUP_PATH;
            $zipFile = $backupDir . '/' . $filename;

            // Verify file exists
            if (!File::exists($zipFile)) {
                throw new Exception('Backup file not found');
            }

            // Extract ZIP
            $zip = new ZipArchive();
            if ($zip->open($zipFile) !== true) {
                throw new Exception('Failed to open backup ZIP file');
            }

            $tempDir = $backupDir . '/temp_' . time();
            File::makeDirectory($tempDir, 0755, true);
            
            $zip->extractTo($tempDir);
            $zip->close();

            // Find SQL file
            $sqlFiles = File::glob($tempDir . '/*.sql');
            if (empty($sqlFiles)) {
                File::deleteDirectory($tempDir);
                throw new Exception('No SQL file found in backup');
            }

            $sqlFile = $sqlFiles[0];

            // Get database credentials
            $host = config('database.connections.mysql.host');
            $port = config('database.connections.mysql.port', 3306);
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $databaseName = config('database.connections.mysql.database');

            // Build mysql import command
            $importCommand = sprintf(
                'mysql --host=%s --port=%s --user=%s --password=%s %s < %s 2>&1',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($databaseName),
                escapeshellarg($sqlFile)
            );

            // Execute import
            exec($importCommand, $output, $returnCode);

            // Clean up temp directory
            File::deleteDirectory($tempDir);

            if ($returnCode !== 0) {
                throw new Exception('Database restore failed: ' . implode("\n", $output));
            }

            Log::info('Database restored successfully', [
                'file' => $filename
            ]);

            return [
                'success' => true,
                'message' => 'Database restored successfully from ' . $filename
            ];

        } catch (Exception $e) {
            Log::error('Backup restore failed', [
                'file' => $filename,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Restore failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * @brief Clean up old backup files
     * @details Deletes backups older than retention period
     * 
     * @return int Number of files deleted
     * 
     * @author UPRM VoIP Monitoring System Team
     * @date November 17, 2025
     */
    private function cleanOldBackups(): int
    {
        $backupDir = self::BACKUP_PATH;
        $deleted = 0;

        if (!File::isDirectory($backupDir)) {
            return 0;
        }

        $files = File::glob($backupDir . '/backup_*.zip');
        $cutoffDate = Carbon::now()->subWeeks(self::RETENTION_WEEKS);

        foreach ($files as $file) {
            $fileDate = Carbon::createFromTimestamp(filemtime($file));
            
            if ($fileDate->lt($cutoffDate)) {
                File::delete($file);
                $deleted++;
                
                Log::info('Old backup deleted', [
                    'file' => basename($file),
                    'age' => $fileDate->diffForHumans()
                ]);
            }
        }

        return $deleted;
    }

    /**
     * @brief Extract timestamp from backup filename
     * @param string $filename
     * @return Carbon|null
     */
    private function extractTimestampFromFilename(string $filename): ?Carbon
    {
        // Expected format: backup_2025-11-17_061612.zip
        if (preg_match('/backup_(\d{4}-\d{2}-\d{2})_(\d{6})\.zip/', $filename, $matches)) {
            $date = $matches[1];
            $time = $matches[2];
            
            // Parse time as HHMMSS
            $hour = substr($time, 0, 2);
            $minute = substr($time, 2, 2);
            $second = substr($time, 4, 2);
            
            try {
                return Carbon::createFromFormat('Y-m-d H:i:s', "$date $hour:$minute:$second");
            } catch (\Exception $e) {
                return null;
            }
        }
        
        return null;
    }

    /**
     * @brief Format bytes to human-readable string
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * @brief Get backup statistics
     * @details Returns summary of backup storage
     * 
     * @return array Storage statistics
     * 
     * @author UPRM VoIP Monitoring System Team
     * @date November 17, 2025
     */
    public function getBackupStats(): array
    {
        $backups = $this->getAllBackups();
        $totalSize = array_sum(array_column($backups, 'size'));

        return [
            'total_backups' => count($backups),
            'total_size' => $totalSize,
            'total_size_formatted' => $this->formatBytes($totalSize),
            'oldest' => !empty($backups) ? end($backups)['created_at'] : null,
            'newest' => !empty($backups) ? $backups[0]['created_at'] : null,
            'retention_weeks' => self::RETENTION_WEEKS
        ];
    }
}
