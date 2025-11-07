<?php

/**
 * @file DataImportService.php
 * @brief Service class for importing and extracting external data files
 * @details Handles extraction of PostgreSQL CSV and MongoDB JSON export files
 *          for ETL processing directly into MariaDB
 * @author UPRM VoIP Monitoring System Team
 * @date November 2, 2025
 * @version 2.3
 */

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

/**
 * @class DataImportService
 * @brief Service for extracting external data files for ETL processing
 * @details Extracts exported data from production server and prepares it
 *          for ETL pipeline to process into MariaDB
 * @author UPRM VoIP Monitoring System Team
 * @date November 2, 2025
 */
class DataImportService
{
    /**
     * @brief Storage path for extracted import files
     * @var string
     */
    protected $importStoragePath;

    /**
     * @brief Constructor initializes storage paths
     * @details Sets up directory for storing extracted import files
     * @author UPRM VoIP Monitoring System Team
     * @date November 2, 2025
     */
    public function __construct()
    {
        // Set import storage path
        $this->importStoragePath = storage_path('app/imports/extracted');
        
        // Ensure directory exists
        if (!File::exists($this->importStoragePath)) {
            File::makeDirectory($this->importStoragePath, 0755, true);
        }
    }

    /**
     * @brief Extract and prepare data from compressed archive
     * @details Extracts tar.gz archive containing PostgreSQL CSV and MongoDB JSON
     *          files, then stores them for ETL processing
     * 
     * @param string $archivePath Path to the tar.gz archive file
     * @return array Paths to extracted files and statistics
     * 
     * @throws Exception If archive extraction fails
     * 
     * @author UPRM VoIP Monitoring System Team
     * @date November 2, 2025
     */
    public function extractArchive(string $archivePath): array
    {
        // Validate archive exists
        if (!file_exists($archivePath)) {
            throw new Exception("Archive not found: {$archivePath}");
        }

        // Create timestamped extraction directory
        $timestamp = date('YmdHis');
        $extractPath = $this->importStoragePath . DIRECTORY_SEPARATOR . "import_{$timestamp}";
        
        // Ensure directory doesn't exist
        if (File::exists($extractPath)) {
            File::deleteDirectory($extractPath);
        }
        
        File::makeDirectory($extractPath, 0755, true);

        try {
            // Try multiple extraction methods
            $extractedCount = 0;
            
            // Method 1: Use Windows built-in tar (available in Windows 10+)
            if (PHP_OS_FAMILY === 'Windows' && $this->hasWindowsTar()) {
                Log::info("Using Windows native tar command");
                $extractedCount = $this->extractUsingWindowsTar($archivePath, $extractPath);
            } 
            // Method 2: Use PharData
            else {
                Log::info("Using PharData extraction");
                $extractedCount = $this->extractUsingPharData($archivePath, $extractPath);
            }

            Log::info("Extracted {$extractedCount} files from archive");

            // List all extracted files
            $extractedFiles = $this->scanExtractedFiles($extractPath);

            // Try to locate required files (may be in subdirectories)
            $usersCsv = $this->findFile($extractPath, 'users.csv');
            $registrarJson = $this->findFile($extractPath, 'registrar.json');
            $metadataJson = $this->findFile($extractPath, 'metadata.json');

            // Check if required files exist
            $filesFound = [
                'users_csv' => !is_null($usersCsv),
                'registrar_json' => !is_null($registrarJson),
                'metadata' => !is_null($metadataJson)
            ];

            // Count records
            $stats = [
                'archive' => basename($archivePath),
                'extract_path' => $extractPath,
                'timestamp' => $timestamp,
                'files_found' => $filesFound,
                'extracted_files' => $extractedFiles,
                'extracted_count' => count($extractedFiles),
                'file_paths' => [
                    'users_csv' => $usersCsv,
                    'registrar_json' => $registrarJson,
                    'metadata' => $metadataJson
                ],
                'record_counts' => [
                    'users' => $filesFound['users_csv'] ? $this->countCsvRows($usersCsv) : 0,
                    'registrations' => $filesFound['registrar_json'] ? $this->countJsonRecords($registrarJson) : 0
                ]
            ];

            // Load metadata if exists
            if ($filesFound['metadata']) {
                $metadata = json_decode(file_get_contents($metadataJson), true);
                $stats['metadata'] = $metadata;
            }

            return $stats;

        } catch (Exception $e) {
            Log::error("Archive extraction failed", [
                'error' => $e->getMessage(),
                'extract_path' => $extractPath
            ]);
            
            throw $e;
        }
    }

    /**
     * @brief Check if Windows tar command is available
     * @details Tests if tar.exe is available in system PATH
     * 
     * @return bool True if tar command is available
     * 
     * @author UPRM VoIP Monitoring System Team
     * @date November 2, 2025
     */
    private function hasWindowsTar(): bool
    {
        $output = [];
        $returnCode = 0;
        exec('tar --version 2>&1', $output, $returnCode);
        return $returnCode === 0;
    }

    /**
     * @brief Extract using Windows native tar command
     * @details Uses Windows 10+ built-in tar.exe for extraction
     * 
     * @param string $archivePath Path to the tar.gz file
     * @param string $extractPath Destination directory for extraction
     * @return int Number of files extracted
     * 
     * @throws Exception If extraction fails
     * 
     * @author UPRM VoIP Monitoring System Team
     * @date November 2, 2025
     */
    private function extractUsingWindowsTar(string $archivePath, string $extractPath): int
    {
        // Escape paths for Windows command line
        $archivePath = escapeshellarg($archivePath);
        $extractPath = escapeshellarg($extractPath);
        
        // Build tar command
        $command = "tar -xzf {$archivePath} -C {$extractPath} 2>&1";
        
        Log::info("Executing tar command", ['command' => $command]);
        
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            $errorMsg = implode("\n", $output);
            Log::error("Tar extraction failed", [
                'return_code' => $returnCode,
                'output' => $errorMsg
            ]);
            throw new Exception("Tar extraction failed (return code: {$returnCode}): {$errorMsg}");
        }
        
        // Count extracted files
        $files = $this->scanExtractedFiles($extractPath);
        return count($files);
    }

    /**
     * @brief Extract using PharData (fallback method)
     * @details Uses PHP's PharData for tar.gz extraction
     * 
     * @param string $archivePath Path to the tar.gz file
     * @param string $extractPath Destination directory for extraction
     * @return int Number of files extracted
     * 
     * @throws Exception If extraction fails
     * 
     * @author UPRM VoIP Monitoring System Team
     * @date November 2, 2025
     */
    private function extractUsingPharData(string $archivePath, string $extractPath): int
    {
        $decompressedTar = null;
        
        try {
            // Step 1: Decompress .gz to .tar
            $phar = new \PharData($archivePath);
            
            $decompressedTar = str_replace('.tar.gz', '.tar', $archivePath);
            $decompressedTar = str_replace('.tgz', '.tar', $decompressedTar);
            
            // Decompress
            $phar->decompress();
            
            if (!file_exists($decompressedTar)) {
                throw new Exception("Decompression failed");
            }
            
            // Step 2: Extract tar
            $tar = new \PharData($decompressedTar);
            
            // Try extractTo with overwrite
            $tar->extractTo($extractPath, null, true);
            
            // Cleanup
            if (file_exists($decompressedTar)) {
                unlink($decompressedTar);
            }
            
            // Count files
            $files = $this->scanExtractedFiles($extractPath);
            return count($files);
            
        } catch (Exception $e) {
            // Cleanup
            if ($decompressedTar && file_exists($decompressedTar)) {
                unlink($decompressedTar);
            }
            
            throw new Exception("PharData extraction failed: " . $e->getMessage());
        }
    }

    /**
     * @brief Scan directory and list all extracted files
     * @details Recursively scans directory and returns list of all files
     * 
     * @param string $directory Directory to scan
     * @return array List of file paths relative to extraction directory
     * 
     * @author UPRM VoIP Monitoring System Team
     * @date November 2, 2025
     */
    private function scanExtractedFiles(string $directory): array
    {
        $files = [];
        
        if (!File::exists($directory)) {
            return $files;
        }

        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $relativePath = str_replace($directory . DIRECTORY_SEPARATOR, '', $file->getPathname());
                    $files[] = $relativePath;
                }
            }
        } catch (Exception $e) {
            Log::warning("Error scanning directory", ['error' => $e->getMessage()]);
        }

        return $files;
    }

    /**
     * @brief Find file by name in directory tree
     * @details Recursively searches for a file by name
     * 
     * @param string $directory Directory to search
     * @param string $filename Filename to find
     * @return string|null Full path to file or null if not found
     * 
     * @author UPRM VoIP Monitoring System Team
     * @date November 2, 2025
     */
    private function findFile(string $directory, string $filename): ?string
    {
        if (!File::exists($directory)) {
            return null;
        }

        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getFilename() === $filename) {
                    return $file->getPathname();
                }
            }
        } catch (Exception $e) {
            Log::warning("Error finding file", [
                'filename' => $filename,
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }

    /**
     * @brief Count rows in CSV file
     * @details Counts data rows (excluding header) in CSV file
     * 
     * @param string $filePath Path to CSV file
     * @return int Number of data rows
     * 
     * @author UPRM VoIP Monitoring System Team
     * @date November 2, 2025
     */
    private function countCsvRows(string $filePath): int
    {
        if (!file_exists($filePath)) {
            return 0;
        }

        $lines = file($filePath);
        return max(0, count($lines) - 1);
    }

    /**
     * @brief Count records in JSON array file
     * @details Counts number of objects in JSON array
     * 
     * @param string $filePath Path to JSON file
     * @return int Number of records
     * 
     * @author UPRM VoIP Monitoring System Team
     * @date November 2, 2025
     */
    private function countJsonRecords(string $filePath): int
    {
        if (!file_exists($filePath)) {
            return 0;
        }

        $content = file_get_contents($filePath);
        $data = json_decode($content, true);
        
        return is_array($data) ? count($data) : 0;
    }

    /**
     * @brief Get list of all extracted imports
     * @details Returns list of all import directories with metadata
     * 
     * @return array List of import information
     * 
     * @author UPRM VoIP Monitoring System Team
     * @date November 2, 2025
     */
    public function listExtracts(): array
    {
        $imports = [];
        
        if (!File::exists($this->importStoragePath)) {
            return $imports;
        }

        $directories = File::directories($this->importStoragePath);
        
        foreach ($directories as $dir) {
            $metadataPath = $this->findFile($dir, 'metadata.json');
            
            $imports[] = [
                'path' => $dir,
                'name' => basename($dir),
                'metadata' => $metadataPath ? json_decode(file_get_contents($metadataPath), true) : null
            ];
        }

        return $imports;
    }

    /**
     * @brief Clean up old extracted imports
     * @details Removes import directories older than specified days
     * 
     * @param int $daysOld Remove imports older than this many days (default: 7)
     * @return int Number of directories removed
     * 
     * @author UPRM VoIP Monitoring System Team
     * @date November 2, 2025
     */
    public function cleanOldExtracts(int $daysOld = 7): int
    {
        $removed = 0;
        
        if (!File::exists($this->importStoragePath)) {
            return $removed;
        }

        $directories = File::directories($this->importStoragePath);
        $cutoffTime = time() - ($daysOld * 24 * 60 * 60);
        
        foreach ($directories as $dir) {
            if (filemtime($dir) < $cutoffTime) {
                File::deleteDirectory($dir);
                $removed++;
            }
        }

        return $removed;
    }
}