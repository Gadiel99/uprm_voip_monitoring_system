<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class UpdateTestStatus extends Command
{
    protected $signature = 'test:update-status';
    protected $description = 'Run tests and automatically update test-cases.csv with actual results';

    public function handle()
    {
        $csvPath = base_path('docs/testing/test-cases.csv');
        
        if (!File::exists($csvPath)) {
            $this->error('test-cases.csv not found! Run: php artisan test:report --format=csv first');
            return Command::FAILURE;
        }
        
        $this->info('Running tests...');
        $this->newLine();
        
        // Run tests and capture results
        $exitCode = Artisan::call('test', [], $this->output);
        
        $this->newLine();
        $this->info('Updating test-cases.csv with results...');
        
        // Get test results
        $testResults = $this->getTestResults();
        
        if (empty($testResults)) {
            $this->warn('Could not parse test results. Marking all as Pass based on exit code.');
            $testResults = $this->markAllByExitCode($exitCode);
        }
        
        // Update CSV
        $this->updateCSV($csvPath, $testResults, $exitCode);
        
        $this->newLine();
        $this->info('Updated test-cases.csv with actual test results!');
        $this->info('File: docs/testing/test-cases.csv');
        $this->info('Date: ' . now()->format('Y-m-d'));
        
        return $exitCode === 0 ? Command::SUCCESS : Command::FAILURE;
    }
    
    private function getTestResults(): array
    {
        // Run tests with JSON output
        $process = new \Symfony\Component\Process\Process(
            ['php', 'artisan', 'test', '--without-tty'],
            base_path()
        );
        
        $process->run();
        $output = $process->getOutput();
        
        $results = [];
        $lines = explode("\n", $output);
        
        foreach ($lines as $line) {
            // Match test results like: ✓ test name or PASS
            if (preg_match('/\s+(✓|✗)\s+(.+?)\s+[\d\.]+/', $line, $matches)) {
                $status = $matches[1] === '✓' ? 'Pass' : 'Fail';
                $testName = trim($matches[2]);
                $results[$testName] = $status;
            }
        }
        
        return $results;
    }
    
    private function markAllByExitCode(int $exitCode): array
    {
        return ['*' => $exitCode === 0 ? 'Pass' : 'Fail'];
    }
    
    private function updateCSV(string $csvPath, array $testResults, int $exitCode): void
    {
        $rows = [];
        $handle = fopen($csvPath, 'r');
        $header = fgetcsv($handle);
        $rows[] = $header;
        
        $passCount = 0;
        $failCount = 0;
        $defaultStatus = $exitCode === 0 ? 'Pass' : 'Fail';
        
        while (($row = fgetcsv($handle)) !== false) {
            $testName = $row[2]; // Test Name column
            
            // Find matching status from test results
            $status = $testResults[$testName] ?? $testResults['*'] ?? $defaultStatus;
            
            // Update status column (index 5)
            $row[5] = $status;
            // Update date tested column (index 6)
            $row[6] = now()->format('Y-m-d');
            
            if ($status === 'Pass') {
                $passCount++;
            } else {
                $failCount++;
            }
            
            $rows[] = $row;
        }
        fclose($handle);
        
        // Write back to CSV
        $handle = fopen($csvPath, 'w');
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);
        
        $this->newLine();
        $this->info("Results: {$passCount} passed, {$failCount} failed");
    }
}
