<?php

/**
 * @file FileCleanupService.php
 * @brief Service for cleaning up old ETL import files
 * @details Manages retention and deletion of archive and extracted files
 * @author UPRM VoIP Monitoring System Team
 * @date November 16, 2025
 * @version 1.0
 */

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

/**
 * @class FileCleanupService
 * @brief Service for cleaning up old import files based on retention policy
 * @details Removes archive and extracted files older than configured retention periods
 * @author UPRM VoIP Monitoring System Team
 * @date November 16, 2025
 */
class FileCleanupService
{
    /**
     * @brief Default retention period for archive files (in days)
     * @var int
     */
    private const ARCHIVE_RETENTION_DAYS = 30;

    /**
     * @brief Default retention period for extracted files (in days)
     * @var int
     */
    private const EXTRACTED_RETENTION_DAYS = 7;

    /**
     * @brief Clean up old files based on retention policy
     * @details Removes archives and extracted directories older than retention periods
     * 
     * @param int|null $archiveRetentionDays Optional override for archive retention (days)
     * @param int|null $extractedRetentionDays Optional override for extracted retention (days)
     * @return array Summary of cleanup results
     * 
     * @author UPRM VoIP Monitoring System Team
     * @date November 16, 2025
     */
    public function cleanup(?int $archiveRetentionDays = null, ?int $extractedRetentionDays = null): array
    {
        $archiveRetentionDays = $archiveRetentionDays ?? self::ARCHIVE_RETENTION_DAYS;
        $extractedRetentionDays = $extractedRetentionDays ?? self::EXTRACTED_RETENTION_DAYS;

        Log::info('Starting file cleanup', [
            'archive_retention_days' => $archiveRetentionDays,
            'extracted_retention_days' => $extractedRetentionDays
        ]);

        $results = [
            'archives_deleted' => 0,
            'archives_size_freed' => 0,
            'extracted_deleted' => 0,
            'extracted_size_freed' => 0,
            'errors' => []
        ];

        // Clean up archive files
        $archiveResults = $this->cleanupArchives($archiveRetentionDays);
        $results['archives_deleted'] = $archiveResults['deleted'];
        $results['archives_size_freed'] = $archiveResults['size_freed'];
        $results['errors'] = array_merge($results['errors'], $archiveResults['errors']);

        // Clean up extracted directories
        $extractedResults = $this->cleanupExtracted($extractedRetentionDays);
        $results['extracted_deleted'] = $extractedResults['deleted'];
        $results['extracted_size_freed'] = $extractedResults['size_freed'];
        $results['errors'] = array_merge($results['errors'], $extractedResults['errors']);

        Log::info('File cleanup completed', $results);

        return $results;
    }

    /**
     * @brief Clean up old archive files
     * @details Removes .tar.gz archives older than retention period
     * 
     * @param int $retentionDays Number of days to retain archives
     * @return array Cleanup statistics
     * 
     * @author UPRM VoIP Monitoring System Team
     * @date November 16, 2025
     */
    private function cleanupArchives(int $retentionDays): array
    {
        $results = [
            'deleted' => 0,
            'size_freed' => 0,
            'errors' => []
        ];

        $archivePath = storage_path('app/imports/archives');

        if (!File::isDirectory($archivePath)) {
            Log::info('Archives directory does not exist', ['path' => $archivePath]);
            return $results;
        }

        $cutoffDate = Carbon::now()->subDays($retentionDays);
        $files = File::files($archivePath);

        foreach ($files as $file) {
            try {
                $filePath = $file->getPathname();
                $fileTime = Carbon::createFromTimestamp($file->getMTime());

                if ($fileTime->lt($cutoffDate)) {
                    $fileSize = $file->getSize();
                    
                    if (File::delete($filePath)) {
                        $results['deleted']++;
                        $results['size_freed'] += $fileSize;
                        
                        Log::info('Deleted old archive', [
                            'file' => $file->getFilename(),
                            'age_days' => $fileTime->diffInDays(Carbon::now()),
                            'size' => $this->formatBytes($fileSize)
                        ]);
                    }
                }
            } catch (\Exception $e) {
                $error = "Failed to delete archive: {$file->getFilename()} - {$e->getMessage()}";
                $results['errors'][] = $error;
                Log::error($error);
            }
        }

        return $results;
    }

    /**
     * @brief Clean up old extracted directories
     * @details Removes extracted import directories older than retention period
     * 
     * @param int $retentionDays Number of days to retain extracted directories
     * @return array Cleanup statistics
     * 
     * @author UPRM VoIP Monitoring System Team
     * @date November 16, 2025
     */
    private function cleanupExtracted(int $retentionDays): array
    {
        $results = [
            'deleted' => 0,
            'size_freed' => 0,
            'errors' => []
        ];

        $extractedPath = storage_path('app/imports/extracted');

        if (!File::isDirectory($extractedPath)) {
            Log::info('Extracted directory does not exist', ['path' => $extractedPath]);
            return $results;
        }

        $cutoffDate = Carbon::now()->subDays($retentionDays);
        $directories = File::directories($extractedPath);

        foreach ($directories as $directory) {
            try {
                $dirTime = Carbon::createFromTimestamp(filemtime($directory));

                if ($dirTime->lt($cutoffDate)) {
                    $dirSize = $this->getDirectorySize($directory);
                    
                    if (File::deleteDirectory($directory)) {
                        $results['deleted']++;
                        $results['size_freed'] += $dirSize;
                        
                        Log::info('Deleted old extracted directory', [
                            'directory' => basename($directory),
                            'age_days' => $dirTime->diffInDays(Carbon::now()),
                            'size' => $this->formatBytes($dirSize)
                        ]);
                    }
                }
            } catch (\Exception $e) {
                $error = "Failed to delete extracted directory: " . basename($directory) . " - {$e->getMessage()}";
                $results['errors'][] = $error;
                Log::error($error);
            }
        }

        return $results;
    }

    /**
     * @brief Calculate total size of a directory
     * @details Recursively sums file sizes in directory
     * 
     * @param string $directory Path to directory
     * @return int Total size in bytes
     * 
     * @author UPRM VoIP Monitoring System Team
     * @date November 16, 2025
     */
    private function getDirectorySize(string $directory): int
    {
        $size = 0;
        
        try {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($files as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }
        } catch (\Exception $e) {
            Log::warning('Could not calculate directory size', [
                'directory' => $directory,
                'error' => $e->getMessage()
            ]);
        }

        return $size;
    }

    /**
     * @brief Format bytes into human-readable string
     * @details Converts bytes to KB, MB, GB, etc.
     * 
     * @param int $bytes Size in bytes
     * @param int $precision Decimal precision
     * @return string Formatted string (e.g., "1.5 MB")
     * 
     * @author UPRM VoIP Monitoring System Team
     * @date November 16, 2025
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
     * @brief Get current storage statistics
     * @details Returns information about current file storage usage
     * 
     * @return array Storage statistics
     * 
     * @author UPRM VoIP Monitoring System Team
     * @date November 16, 2025
     */
    public function getStorageStats(): array
    {
        $stats = [
            'archives' => [
                'count' => 0,
                'size' => 0,
                'oldest' => null,
                'newest' => null
            ],
            'extracted' => [
                'count' => 0,
                'size' => 0,
                'oldest' => null,
                'newest' => null
            ]
        ];

        // Archive stats
        $archivePath = storage_path('app/imports/archives');
        if (File::isDirectory($archivePath)) {
            $files = File::files($archivePath);
            $stats['archives']['count'] = count($files);
            
            $times = [];
            foreach ($files as $file) {
                $stats['archives']['size'] += $file->getSize();
                $times[] = $file->getMTime();
            }
            
            if (!empty($times)) {
                $stats['archives']['oldest'] = Carbon::createFromTimestamp(min($times))->toDateTimeString();
                $stats['archives']['newest'] = Carbon::createFromTimestamp(max($times))->toDateTimeString();
            }
        }

        // Extracted stats
        $extractedPath = storage_path('app/imports/extracted');
        if (File::isDirectory($extractedPath)) {
            $directories = File::directories($extractedPath);
            $stats['extracted']['count'] = count($directories);
            
            $times = [];
            foreach ($directories as $directory) {
                $stats['extracted']['size'] += $this->getDirectorySize($directory);
                $times[] = filemtime($directory);
            }
            
            if (!empty($times)) {
                $stats['extracted']['oldest'] = Carbon::createFromTimestamp(min($times))->toDateTimeString();
                $stats['extracted']['newest'] = Carbon::createFromTimestamp(max($times))->toDateTimeString();
            }
        }

        // Format sizes
        $stats['archives']['size_formatted'] = $this->formatBytes($stats['archives']['size']);
        $stats['extracted']['size_formatted'] = $this->formatBytes($stats['extracted']['size']);

        return $stats;
    }
}
