<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class GenerateTestReport extends Command
{
    protected $signature = 'test:report {--format=csv : Output format (csv, markdown, both)}';
    protected $description = 'Generate test report in CSV or Markdown format by scanning test files';

    public function handle()
    {
        $this->info('Scanning test files...');
        
        $tests = $this->scanTestFiles();
        $format = $this->option('format');
        
        $this->ensureDocsDirectory();
        
        if ($format === 'both') {
            $this->generateCSV($tests);
            $this->generateMarkdown($tests);
        } elseif ($format === 'markdown') {
            $this->generateMarkdown($tests);
        } else {
            $this->generateCSV($tests);
        }
        
        $this->newLine();
        $this->info('Test report generated successfully!');
        $this->info('Total test cases found: ' . count($tests));
    }

    private function scanTestFiles(): array
    {
        $testFiles = [];
        $counter = 1;
        
        // Scan Unit tests
        if (File::exists(base_path('tests/Unit'))) {
            $unitTests = File::allFiles(base_path('tests/Unit'));
            foreach ($unitTests as $file) {
                $tests = $this->extractTestsFromFile($file, 'Unit', $counter);
                $testFiles = array_merge($testFiles, $tests);
                $counter += count($tests);
            }
        }
        
        // Scan Feature tests
        if (File::exists(base_path('tests/Feature'))) {
            $featureTests = File::allFiles(base_path('tests/Feature'));
            foreach ($featureTests as $file) {
                $tests = $this->extractTestsFromFile($file, 'Feature', $counter);
                $testFiles = array_merge($testFiles, $tests);
                $counter += count($tests);
            }
        }
        
        return $testFiles;
    }

    private function extractTestsFromFile($file, string $type, int $startCounter): array
    {
        $content = File::get($file->getPathname());
        $tests = [];
        
        // Extract test cases using regex for both test() and it() syntax
        preg_match_all("/(?:test|it)\s*\(\s*['\"](.+?)['\"]/", $content, $matches);
        
        $testCases = $matches[1] ?? [];
        
        foreach ($testCases as $index => $testCase) {
            $tests[] = [
                'id' => 'TC-' . str_pad($startCounter + $index, 3, '0', STR_PAD_LEFT),
                'file' => str_replace('.php', '', $file->getFilename()),
                'module' => $this->extractModule($file->getPath()),
                'test_name' => $testCase,
                'type' => $type,
                'priority' => $this->determinePriority($testCase, $file->getPath()),
                'status' => 'Pending',
                'date_tested' => '',
                'notes' => ''
            ];
        }
        
        return $tests;
    }

    private function generateCSV(array $tests): void
    {
        $csvPath = base_path('docs/testing/test-cases.csv');
        
        $handle = fopen($csvPath, 'w');
        
        // Header
        fputcsv($handle, [
            'Test ID',
            'Module',
            'Test Name',
            'Type',
            'Priority',
            'Status',
            'Date Tested',
            'Notes'
        ]);
        
        // Data rows
        foreach ($tests as $test) {
            fputcsv($handle, [
                $test['id'],
                $test['module'],
                $test['test_name'],
                $test['type'],
                $test['priority'],
                $test['status'],
                $test['date_tested'],
                $test['notes']
            ]);
        }
        
        fclose($handle);
        
        $this->info("CSV report saved to: docs/testing/test-cases.csv");
    }

    private function generateMarkdown(array $tests): void
    {
        $mdPath = base_path('docs/testing/TEST_CASES.md');
        
        $content = "# Test Cases Documentation\n\n";
        $content .= "**Generated:** " . now()->format('Y-m-d H:i:s') . "\n\n";
        $content .= "**Total Tests:** " . count($tests) . "\n\n";
        
        // Summary by type
        $unitCount = collect($tests)->where('type', 'Unit')->count();
        $featureCount = collect($tests)->where('type', 'Feature')->count();
        
        $content .= "## Summary\n\n";
        $content .= "| Type | Count |\n";
        $content .= "|------|-------|\n";
        $content .= "| Unit Tests | {$unitCount} |\n";
        $content .= "| Feature Tests | {$featureCount} |\n\n";
        
        // Group by module
        $grouped = collect($tests)->groupBy('module');
        
        foreach ($grouped as $module => $moduleTests) {
            $content .= "## {$module}\n\n";
            $content .= "| Test ID | Test Name | Type | Priority | Status |\n";
            $content .= "|---------|-----------|------|----------|--------|\n";
            
            foreach ($moduleTests as $test) {
                $content .= sprintf(
                    "| %s | %s | %s | %s | %s |\n",
                    $test['id'],
                    $test['test_name'],
                    $test['type'],
                    $test['priority'],
                    $test['status']
                );
            }
            
            $content .= "\n";
        }
        
        File::put($mdPath, $content);
        
        $this->info("Markdown report saved to: docs/testing/TEST_CASES.md");
    }

    private function extractModule(string $path): string
    {
        if (str_contains($path, 'Models')) return 'Models';
        if (str_contains($path, 'Services')) return 'Services';
        if (str_contains($path, 'Controllers')) return 'Controllers';
        if (str_contains($path, 'Database')) return 'Database';
        if (str_contains($path, 'Commands')) return 'Commands';
        return 'General';
    }

    private function determinePriority(string $testName, string $path): string
    {
        $testLower = strtolower($testName);
        
        // Critical tests
        if (str_contains($testLower, 'etl') || 
            str_contains($testLower, 'critical') ||
            str_contains($path, 'ETL')) {
            return 'Critical';
        }
        
        // High priority tests
        if (str_contains($testLower, 'relationship') || 
            str_contains($testLower, 'create') ||
            str_contains($testLower, 'unique') ||
            str_contains($testLower, 'validation') ||
            str_contains($path, 'Models')) {
            return 'High';
        }
        
        // Medium priority tests
        if (str_contains($testLower, 'update') ||
            str_contains($testLower, 'delete')) {
            return 'Medium';
        }
        
        return 'Low';
    }

    private function ensureDocsDirectory(): void
    {
        $docsPath = base_path('docs/testing');
        
        if (!File::isDirectory($docsPath)) {
            File::makeDirectory($docsPath, 0755, true);
            $this->info('Created docs/testing directory');
        }
    }
}
