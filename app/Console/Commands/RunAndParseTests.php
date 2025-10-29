<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class RunAndParseTests extends Command
{
    protected $signature = 'test:run-parse {--format=csv : Output format (csv, markdown, both)}';
    protected $description = 'Run tests and parse results into a report';

    public function handle()
    {
        $this->info('Running tests...');
        $this->newLine();
        
        // Run tests and capture output
        $result = Process::run('./vendor/bin/pest --no-coverage');
        
        $output = $result->output();
        $this->line($output);
        
        $this->newLine();
        $this->info('Parsing test results...');
        
        // Parse output
        $testResults = $this->parseTestOutput($output);
        
        // Generate report
        $format = $this->option('format');
        $this->ensureDocsDirectory();
        
        if ($format === 'both') {
            $this->generateCSV($testResults);
            $this->generateMarkdown($testResults);
        } elseif ($format === 'markdown') {
            $this->generateMarkdown($testResults);
        } else {
            $this->generateCSV($testResults);
        }
        
        $this->newLine();
        $this->info('Test results parsed and saved!');
        
        // Display summary
        $passed = collect($testResults)->where('status', 'Pass')->count();
        $failed = collect($testResults)->where('status', 'Fail')->count();
        $total = count($testResults);
        
        $this->newLine();
        $this->info("Summary:");
        $this->info("   Total: {$total}");
        $this->info("   Passed: {$passed}");
        $this->info("   Failed: {$failed}");
        
        return $failed === 0 ? Command::SUCCESS : Command::FAILURE;
    }

    private function parseTestOutput(string $output): array
    {
        $tests = [];
        $lines = explode("\n", $output);
        $counter = 1;
        $currentFile = '';
        
        foreach ($lines as $line) {
            // Detect test file
            if (preg_match('/PASS.*Tests\\\\(Unit|Feature)\\\\(.+)/', $line, $fileMatch)) {
                $currentFile = $fileMatch[2];
            }
            
            if (preg_match('/FAIL.*Tests\\\\(Unit|Feature)\\\\(.+)/', $line, $fileMatch)) {
                $currentFile = $fileMatch[2];
            }
            
            // Detect individual test results
            if (preg_match('/^\s*(✓|✗)\s+(.+)/', $line, $matches)) {
                $status = $matches[1] === '✓' ? 'Pass' : 'Fail';
                $testName = trim($matches[2]);
                
                $tests[] = [
                    'id' => 'TC-' . str_pad($counter, 3, '0', STR_PAD_LEFT),
                    'file' => $currentFile,
                    'test_name' => $testName,
                    'status' => $status,
                    'date_tested' => now()->format('Y-m-d'),
                    'time_tested' => now()->format('H:i:s'),
                ];
                
                $counter++;
            }
        }
        
        return $tests;
    }

    private function generateCSV(array $tests): void
    {
        $csvPath = base_path('docs/testing/test-results.csv');
        
        $handle = fopen($csvPath, 'w');
        
        fputcsv($handle, [
            'Test ID',
            'File',
            'Test Name',
            'Status',
            'Date Tested',
            'Time Tested'
        ]);
        
        foreach ($tests as $test) {
            fputcsv($handle, [
                $test['id'],
                $test['file'],
                $test['test_name'],
                $test['status'],
                $test['date_tested'],
                $test['time_tested']
            ]);
        }
        
        fclose($handle);
        
        $this->info("CSV results saved to: docs/testing/test-results.csv");
    }

    private function generateMarkdown(array $tests): void
    {
        $mdPath = base_path('docs/testing/TEST_RESULTS.md');
        
        $passed = collect($tests)->where('status', 'Pass')->count();
        $failed = collect($tests)->where('status', 'Fail')->count();
        $total = count($tests);
        $successRate = $total > 0 ? round(($passed / $total) * 100, 2) : 0;
        
        $content = "# Test Execution Results\n\n";
        $content .= "**Test Run Date:** " . now()->format('Y-m-d') . "\n";
        $content .= "**Test Run Time:** " . now()->format('H:i:s') . "\n\n";
        
        $content .= "## Summary\n\n";
        $content .= "| Metric | Value |\n";
        $content .= "|--------|-------|\n";
        $content .= "| Total Tests | {$total} |\n";
        $content .= "| Passed | {$passed} |\n";
        $content .= "| Failed | {$failed} |\n";
        $content .= "| Success Rate | {$successRate}% |\n\n";
        
        // Group by file
        $grouped = collect($tests)->groupBy('file');
        
        $content .= "## Test Details\n\n";
        
        foreach ($grouped as $file => $fileTests) {
            $content .= "### {$file}\n\n";
            $content .= "| Test ID | Test Name | Status |\n";
            $content .= "|---------|-----------|--------|\n";
            
            foreach ($fileTests as $test) {
                $content .= sprintf(
                    "| %s | %s | %s |\n",
                    $test['id'],
                    $test['test_name'],
                    $test['status']
                );
            }
            
            $content .= "\n";
        }
        
        // Failed tests section
        $failedTests = collect($tests)->where('status', 'Fail');
        
        if ($failedTests->count() > 0) {
            $content .= "## Failed Tests\n\n";
            $content .= "| Test ID | File | Test Name |\n";
            $content .= "|---------|------|--------|\n";
            
            foreach ($failedTests as $test) {
                $content .= sprintf(
                    "| %s | %s | %s |\n",
                    $test['id'],
                    $test['file'],
                    $test['test_name']
                );
            }
            
            $content .= "\n";
        }
        
        File::put($mdPath, $content);
        
        $this->info("Markdown results saved to: docs/testing/TEST_RESULTS.md");
    }

    private function ensureDocsDirectory(): void
    {
        $docsPath = base_path('docs/testing');
        
        if (!File::isDirectory($docsPath)) {
            File::makeDirectory($docsPath, 0755, true);
        }
    }
}
