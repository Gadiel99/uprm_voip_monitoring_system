<?php
// filepath: app/Services/DataImportService.php

namespace App\Services;

use Illuminate\Support\Facades\File;
use PharData;

class DataImportService
{
    private string $importPath;

    public function __construct()
    {
        $this->importPath = storage_path('app/imports');
    }

    /**
     * Extract archive and return statistics
     */
    public function extractArchive(string $archivePath): array
    {
        $timestamp = now()->format('YmdHis');
        $extractPath = "{$this->importPath}/extracted/import_{$timestamp}";

        // Create extraction directory
        File::makeDirectory($extractPath, 0755, true, true);

        try {
            // Extract tar.gz archive using system tar command (more reliable)
            $command = sprintf(
                'tar -xzf %s -C %s --strip-components=1 2>&1',
                escapeshellarg($archivePath),
                escapeshellarg($extractPath)
            );
            
            exec($command, $output, $returnCode);
            
            if ($returnCode !== 0) {
                throw new \Exception('Failed to extract archive: ' . implode("\n", $output));
            }

            // Gather statistics
            $stats = [
                'archive' => basename($archivePath),
                'timestamp' => $timestamp,
                'extract_path' => $extractPath,
                'extracted_files' => [],
                'files_found' => [
                    'users_csv' => false,
                    'registrar_json' => false,
                    'metadata' => false,
                ],
                'record_counts' => [
                    'users' => 0,
                    'registrations' => 0,
                ],
                'metadata' => null,
            ];

            // Check for required files and count records
            $files = File::files($extractPath);
            
            foreach ($files as $file) {
                $filename = $file->getFilename();
                $stats['extracted_files'][] = $filename;

                if ($filename === 'users.csv') {
                    $stats['files_found']['users_csv'] = true;
                    $stats['record_counts']['users'] = $this->countCsvRecords($file->getPathname());
                }

                if ($filename === 'registrar.json') {
                    $stats['files_found']['registrar_json'] = true;
                    $stats['record_counts']['registrations'] = $this->countJsonRecords($file->getPathname());
                }

                if ($filename === 'metadata.json') {
                    $stats['files_found']['metadata'] = true;
                    $stats['metadata'] = json_decode(file_get_contents($file->getPathname()), true);
                }
            }

            return $stats;

        } catch (\Exception $e) {
            // Cleanup on failure
            if (File::exists($extractPath)) {
                File::deleteDirectory($extractPath);
            }
            throw $e;
        }
    }

    /**
     * Count records in CSV file
     */
    private function countCsvRecords(string $filePath): int
    {
        $count = 0;
        $file = fopen($filePath, 'r');
        
        // Skip header
        fgetcsv($file);
        
        while (fgetcsv($file) !== false) {
            $count++;
        }
        
        fclose($file);
        return $count;
    }

    /**
     * Count records in JSON file
     */
    private function countJsonRecords(string $filePath): int
    {
        $content = file_get_contents($filePath);
        $data = json_decode($content, true);
        
        return is_array($data) ? count($data) : 0;
    }

    /**
     * List all extracted imports
     */
    public function listExtracts(): array
    {
        $extractPath = "{$this->importPath}/extracted";
        
        if (!File::exists($extractPath)) {
            return [];
        }

        $directories = File::directories($extractPath);
        $imports = [];

        foreach ($directories as $dir) {
            $metadataPath = "{$dir}/metadata.json";
            $metadata = File::exists($metadataPath) 
                ? json_decode(File::get($metadataPath), true) 
                : [];

            $imports[] = [
                'name' => basename($dir),
                'path' => $dir,
                'created' => File::lastModified($dir),
                'metadata' => $metadata,
            ];
        }

        // Sort by creation date (newest first)
        usort($imports, fn($a, $b) => $b['created'] - $a['created']);

        return $imports;
    }

    /**
     * Clean old extracted imports
     */
    public function cleanOldExtracts(int $daysOld = 7): int
    {
        $extractPath = "{$this->importPath}/extracted";
        
        if (!File::exists($extractPath)) {
            return 0;
        }

        $directories = File::directories($extractPath);
        $cutoff = now()->subDays($daysOld)->timestamp;
        $removed = 0;

        foreach ($directories as $dir) {
            if (File::lastModified($dir) < $cutoff) {
                File::deleteDirectory($dir);
                $removed++;
            }
        }

        return $removed;
    }
}
