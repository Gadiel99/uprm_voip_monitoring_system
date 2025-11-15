<?php

namespace App\Console\Commands;

use App\Services\DataImportService;
use Illuminate\Console\Command;

class ImportData extends Command
{
    protected $signature = 'data:import 
                            {file : Path to the export archive file (.tar.gz)}
                            {--list : List all extracted imports}
                            {--clean : Clean old extracted imports (older than 7 days)}';

    protected $description = 'Extract PostgreSQL and MongoDB export files for ETL processing';

    public function handle(DataImportService $importService): int
    {
        // Handle --list option
        if ($this->option('list')) {
            return $this->listExtracts($importService);
        }

        // Handle --clean option
        if ($this->option('clean')) {
            return $this->cleanExtracts($importService);
        }

        $filePath = $this->argument('file');

        // Validate file exists
        if (!file_exists($filePath)) {
            $this->error("❌ File not found: {$filePath}");
            return self::FAILURE;
        }

        $this->info("📦 Extracting data from: {$filePath}");
        $this->newLine();

        try {
            // Extract archive
            $stats = $importService->extractArchive($filePath);

            // Display extraction results
            $this->displayExtractionStats($stats);

            // Show extracted files list
            if (!empty($stats['extracted_files'])) {
                $this->newLine();
                $this->info("📂 Extracted Files:");
                foreach ($stats['extracted_files'] as $file) {
                    $this->line("   " . $file);
                }
            }

            $this->newLine();
            
            if (!$stats['files_found']['users_csv'] || !$stats['files_found']['registrar_json']) {
                $this->warn('⚠️  Warning: Required files not found in archive!');
                $this->info('   Expected: users.csv and registrar.json');
                $this->info('   Please check the archive structure and re-export from server.');
                return self::FAILURE;
            }
            
            $this->info('✅ Extraction completed successfully!');
            $this->info('📂 Files extracted to: ' . $stats['extract_path']);
            $this->newLine();
            
            // Show next steps
            $this->info('📋 Next Steps:');
            $this->line('   Run ETL to process data into MariaDB:');
            $this->line('');
            $this->comment('      php artisan etl:run --import=' . $stats['extract_path']);
            $this->line('');
            
            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error('❌ Extraction failed: ' . $e->getMessage());
            
            if ($this->output->isVerbose()) {
                $this->error($e->getTraceAsString());
            }
            
            return self::FAILURE;
        }
    }

    private function displayExtractionStats(array $stats): void
    {
        $this->info("📊 Extraction Summary:");
        $this->table(
            ['Item', 'Value'],
            [
                ['Archive', $stats['archive']],
                ['Timestamp', $stats['timestamp']],
                ['Users CSV', $stats['files_found']['users_csv'] ? '✅ Found' : '❌ Missing'],
                ['Registrar JSON', $stats['files_found']['registrar_json'] ? '✅ Found' : '❌ Missing'],
                ['Metadata', $stats['files_found']['metadata'] ? '✅ Found' : '⚠️ Missing'],
            ]
        );

        $this->newLine();
        $this->info("📈 Record Counts:");
        $this->table(
            ['Source', 'Records'],
            [
                ['PostgreSQL Users', number_format($stats['record_counts']['users'])],
                ['MongoDB Registrations', number_format($stats['record_counts']['registrations'])],
            ]
        );

        // Display metadata if available
        if (isset($stats['metadata'])) {
            $this->newLine();
            $this->info("ℹ️ Export Metadata:");
            $this->line("   Export Date: " . ($stats['metadata']['export_date'] ?? 'Unknown'));
            $this->line("   Source Server: " . ($stats['metadata']['server'] ?? 'Unknown'));
            $this->line("   Export Version: " . ($stats['metadata']['export_version'] ?? 'Unknown'));
        }
    }

    private function listExtracts(DataImportService $importService): int
    {
        $imports = $importService->listExtracts();

        if (empty($imports)) {
            $this->info("📭 No extracted imports found.");
            return self::SUCCESS;
        }

        $this->info("📂 Extracted Imports:");
        $this->newLine();

        $tableData = [];
        foreach ($imports as $import) {
            $metadata = $import['metadata'];
            $tableData[] = [
                $import['name'],
                $metadata['export_date'] ?? 'Unknown',
                $metadata['server'] ?? 'Unknown',
            ];
        }

        $this->table(
            ['Directory', 'Export Date', 'Source Server'],
            $tableData
        );

        return self::SUCCESS;
    }

    private function cleanExtracts(DataImportService $importService): int
    {
        $this->info("🧹 Cleaning old extracted imports...");
        
        $removed = $importService->cleanOldExtracts(7);
        
        if ($removed > 0) {
            $this->info("✅ Removed {$removed} old import(s)");
        } else {
            $this->info("✨ No old imports to clean");
        }

        return self::SUCCESS;
    }
}