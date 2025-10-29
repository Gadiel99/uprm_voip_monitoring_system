<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use App\Models\User;
use App\Models\Buildings;
use App\Models\Networks;
use App\Models\Devices;

class AdminController extends Controller
{
    /**
     * Show the admin dashboard populated from the database.
     */
    public function index(Request $request)
    {
        // Simple, resilient counts (if a table doesn't exist yet, catch and set 0)
        $stats = [
            'users' => $this->safeCount(User::class),
            'buildings' => $this->safeCount(Buildings::class),
            'networks' => $this->safeCount(Networks::class),
            'devices' => $this->safeCount(Devices::class),
        ];

        // Recent users (fallback to empty if table missing)
        $recentUsers = $this->safeLatest(User::class, 8);

        // Buildings with network/device summaries (best-effort)
        $buildings = $this->safeAll(Buildings::class, ['building_id','name','code','created_at']);

        // Try to read last ~100 lines of the laravel.log for the "Logs" tab
        $logLines = $this->tailLaravelLog(100);

        return view('pages.admin', [
            'stats' => $stats,
            'recentUsers' => $recentUsers,
            'buildings' => $buildings,
            'logLines' => $logLines,
        ]);
    }

    private function safeCount(string $modelClass): int
    {
        try {
            return $modelClass::count();
        } catch (\Throwable $e) {
            Log::warning('safeCount failed for '.$modelClass.': '.$e->getMessage());
            return 0;
        }
    }

    private function safeLatest(string $modelClass, int $limit = 8)
    {
        try {
            return $modelClass::query()->latest()->limit($limit)->get();
        } catch (\Throwable $e) {
            Log::warning('safeLatest failed for '.$modelClass.': '.$e->getMessage());
            return collect();
        }
    }

    private function safeAll(string $modelClass, array $columns = ['*'])
    {
        try {
            return $modelClass::query()->select($columns)->get();
        } catch (\Throwable $e) {
            Log::warning('safeAll failed for '.$modelClass.': '.$e->getMessage());
            return collect();
        }
    }

    private function tailLaravelLog(int $lines = 100): array
    {
        $logFile = storage_path('logs/laravel.log');
        if (!File::exists($logFile)) {
            return [];
        }

        // Efficient tail
        $buffer = 8192;
        $f = fopen($logFile, 'rb');
        if (!$f) return [];

        fseek($f, 0, SEEK_END);
        $pos = ftell($f);
        $data = '';
        $lineCount = 0;

        while ($pos > 0 && $lineCount <= $lines) {
            $read = max($pos - $buffer, 0);
            $chunkSize = $pos - $read
            ;
            fseek($f, $read);
            $data = fread($f, $chunkSize) . $data;
            $pos = $read;
            $lineCount = substr_count($data, "\n");
        }
        fclose($f);

        $arr = explode("\n", trim($data));
        return array_slice($arr, -$lines);
    }
}
