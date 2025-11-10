<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use App\Models\User;
use App\Models\Buildings;
use App\Models\Networks;
use App\Models\Devices;

/**
 * Controlador del panel de administración (dashboard).
 *
 * - Recolecta métricas básicas (usuarios, edificios, redes, dispositivos).
 * - Obtiene listados recientes y colecciones “best-effort” (si falta tabla, devuelve vacío).
 * - Extrae un tail del archivo laravel.log para el tab de Logs.
 *
 * Nota: Todos los métodos “safe*” atrapan excepciones para no romper la vista si faltan tablas/seeds.
 */
class AdminController extends Controller
{
    /**
     * Renderiza el dashboard de administración con datos de BD y logs recientes.
     *
     * @param  Request  $request
     * @return \Illuminate\Contracts\View\View
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
        
        // Get system logs from session
        $systemLogs = session()->get('system_logs', []);
        
        // Flag to let Blade know if we should show server-driven Users section
        $isUsersServer = true;

        return view('pages.admin', [
            'stats'         => $stats,
            'recentUsers'   => $recentUsers,
            'buildings'     => $buildings,
            'logLines'      => $logLines,
            'systemLogs'    => $systemLogs,
            'isUsersServer' => $isUsersServer,
            'users'         => $recentUsers, // fallback for now; AdminUserController overrides
            'activeTab'     => $request->get('tab') // optional active tab switching
        ]);
    }

    /**
     * Cuenta registros de un modelo de forma segura.
     *
     * @param  class-string $modelClass
     * @return int
     */
    private function safeCount(string $modelClass): int
    {
        try {
            return $modelClass::count();
        } catch (\Throwable $e) {
            Log::warning('safeCount failed for '.$modelClass.': '.$e->getMessage());
            return 0;
        }
    }

    /**
     * Obtiene los últimos N registros de un modelo de forma segura.
     *
     * @param  class-string $modelClass
     * @param  int          $limit
     * @return \Illuminate\Support\Collection
     */
    private function safeLatest(string $modelClass, int $limit = 8)
    {
        try {
            return $modelClass::query()->latest()->limit($limit)->get();
        } catch (\Throwable $e) {
            Log::warning('safeLatest failed for '.$modelClass.': '.$e->getMessage());
            return collect();
        }
    }

    /**
     * Obtiene todos los registros (columnas específicas) de forma segura.
     *
     * @param  class-string $modelClass
     * @param  array<string> $columns
     * @return \Illuminate\Support\Collection
     */
    private function safeAll(string $modelClass, array $columns = ['*'])
    {
        try {
            return $modelClass::query()->select($columns)->get();
        } catch (\Throwable $e) {
            Log::warning('safeAll failed for '.$modelClass.': '.$e->getMessage());
            return collect();
        }
    }

    /**
     * Lee eficientemente las últimas N líneas del log de Laravel.
     *
     * @param  int $lines
     * @return array<int,string>
     */
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
