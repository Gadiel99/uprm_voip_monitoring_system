<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Building;
use App\Models\Network;
use App\Models\Devices;
use App\Models\AlertSettings;

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
            'buildings' => $this->safeCount(Building::class),
            'networks' => $this->safeCount(Network::class),
            'devices' => $this->safeCount(Devices::class),
        ];

        // Recent users (fallback to empty if table missing)
        $recentUsers = $this->safeLatest(User::class, 8);

        // Buildings with network/device summaries (best-effort)
        $buildings = $this->safeAll(Building::class, ['building_id','name','code','created_at']);

        // Try to read last ~100 lines of the laravel.log for the "Logs" tab
        $logLines = $this->tailLaravelLog(100);
        
        // Get system logs from session
        $systemLogs = session()->get('system_logs', []);
        
        // Get alert settings for Settings tab
        $alertSettings = AlertSettings::current();
        
        // Get critical devices for Settings tab
        $criticalDevices = Devices::where('is_critical', true)
            ->select('device_id', 'ip_address', 'mac_address', 'status')
            ->orderBy('ip_address')
            ->get();
        
        // Get extensions for critical devices to show owner names
        $criticalDeviceIds = $criticalDevices->pluck('device_id');
        $extensionsByCriticalDevice = $criticalDeviceIds->isEmpty()
            ? collect()
            : DB::table('device_extensions as de')
                ->join('extensions as e', 'e.extension_id', '=', 'de.extension_id')
                ->whereIn('de.device_id', $criticalDeviceIds)
                ->select('de.device_id', 'e.extension_number', 'e.user_first_name', 'e.user_last_name')
                ->get()
                ->groupBy('device_id');
        
        // Get available devices (not already critical) for dropdown
        $availableDevices = Devices::where('is_critical', false)
            ->orderBy('ip_address')
            ->get();
        
        // Flag to let Blade know if we should show server-driven Users section
        $isUsersServer = true;

        return view('pages.admin', [
            'stats'         => $stats,
            'recentUsers'   => $recentUsers,
            'buildings'     => $buildings,
            'logLines'      => $logLines,
            'systemLogs'    => $systemLogs,
            'alertSettings' => $alertSettings,
            'criticalDevices' => $criticalDevices,
            'extensionsByCriticalDevice' => $extensionsByCriticalDevice,
            'availableDevices' => $availableDevices,
            'isUsersServer' => $isUsersServer,
            'users'         => $recentUsers, // fallback for now; AdminUserController overrides
            'activeTab'     => $request->get('tab') // optional active tab switching
        ]);
    }

    /**
     * Update alert threshold settings.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateAlertSettings(Request $request)
    {
        // Use manual validator so we can control redirect target and tab persistence
    $validator = Validator::make($request->all(), AlertSettings::rules(), [
            'upper_threshold.gt' => 'Upper threshold must be greater than lower threshold.',
        ]);

        if ($validator->fails()) {
            $this->addSystemLog('ERROR', 'Failed to update alert thresholds: ' . $validator->errors()->first());
            // Always return to Admin page with Settings tab active and inline errors
            return redirect()
                ->route('admin', ['tab' => 'settings'])
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();

        $settings = AlertSettings::current();
        $oldLower = $settings->lower_threshold;
        $oldUpper = $settings->upper_threshold;
        $oldActive = $settings->is_active;
        
        $settings->update([
            'lower_threshold' => $validated['lower_threshold'],
            'upper_threshold' => $validated['upper_threshold'],
            'is_active' => true, // Always active since toggle was removed
        ]);
        
        // Log the changes
        $changes = [];
        if ($oldLower != $validated['lower_threshold']) {
            $changes[] = "Lower threshold: {$oldLower}% → {$validated['lower_threshold']}%";
        }
        if ($oldUpper != $validated['upper_threshold']) {
            $changes[] = "Upper threshold: {$oldUpper}% → {$validated['upper_threshold']}%";
        }
        if ($oldActive != $request->boolean('is_active')) {
            $status = $request->boolean('is_active') ? 'enabled' : 'disabled';
            $changes[] = "Alerts {$status}";
        }
        
        if (!empty($changes)) {
            $this->addSystemLog('SUCCESS', 'Alert thresholds updated: ' . implode(', ', $changes));
        }

        return redirect()->route('admin', ['tab' => 'settings'])
            ->with('alert_settings_status', 'Alert settings updated successfully.');
    }

    /**
     * Mark an existing device as critical.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeCriticalDevice(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|exists:devices,device_id',
        ]);

        $device = Devices::findOrFail($validated['device_id']);
        
        // Check if already critical
        if ($device->is_critical) {
            $this->addSystemLog('WARNING', "Attempted to add device {$device->ip_address} to critical list, but it's already marked as critical");
            return redirect()->route('admin', ['tab' => 'settings'])
                ->with('error', 'Device is already marked as critical.');
        }

        $device->update(['is_critical' => true]);
        
        $this->addSystemLog('SUCCESS', "Device added to critical list: {$device->ip_address} ({$device->mac_address})");

        return redirect()->route('admin', ['tab' => 'settings'])
            ->with('alert_settings_status', 'Device added to critical list successfully.');
    }

    /**
     * Remove a device from critical list (set is_critical to false).
     *
     * @param  int  $deviceId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyCriticalDevice($deviceId)
    {
        $device = Devices::findOrFail($deviceId);
        
        // Only allow removal if device is currently critical
        if (!$device->is_critical) {
            $this->addSystemLog('WARNING', "Attempted to remove device {$device->ip_address} from critical list, but it's not marked as critical");
            return redirect()->route('admin', ['tab' => 'settings'])
                ->with('error', 'Device is not marked as critical.');
        }

        // Set is_critical to false instead of deleting
        $device->update(['is_critical' => false]);
        
        $this->addSystemLog('INFO', "Device removed from critical list: {$device->ip_address} ({$device->mac_address})");

        return redirect()->route('admin', ['tab' => 'settings'])
            ->with('alert_settings_status', 'Device removed from critical list successfully.');
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

    /**
     * Add a log entry to the system logs (stored in session/localStorage).
     *
     * @param  string $type (INFO, SUCCESS, WARNING, ERROR)
     * @param  string $message
     * @param  string $user
     * @return void
     */
    private function addSystemLog(string $type, string $message, string $user = 'Admin'): void
    {
        $logs = session()->get('system_logs', []);
        
        $newLog = [
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'type' => $type,
            'message' => $message,
            'user' => $user,
            'id' => time() . rand(1000, 9999)
        ];
        
        array_unshift($logs, $newLog); // Add to beginning
        
        // Keep only last 500 logs
        if (count($logs) > 500) {
            array_pop($logs);
        }
        
        session()->put('system_logs', $logs);
    }

    /**
     * Get critical devices with their current status (for notifications API)
     */
    public function getCriticalDevicesStatus()
    {
        $criticalDevices = Devices::where('is_critical', true)
            ->select('device_id', 'ip_address', 'mac_address', 'status')
            ->get();

        // Get owner names from extensions
        $devices = $criticalDevices->map(function($device) {
            $extension = DB::table('device_extensions as de')
                ->join('extensions as e', 'e.extension_id', '=', 'de.extension_id')
                ->where('de.device_id', $device->device_id)
                ->select('e.user_first_name', 'e.user_last_name')
                ->first();
            
            return [
                'ip' => $device->ip_address,
                'mac' => $device->mac_address,
                'owner' => $extension 
                    ? trim($extension->user_first_name . ' ' . $extension->user_last_name)
                    : 'N/A',
                'status' => ucfirst($device->status) // Capitalize: offline -> Offline, online -> Online
            ];
        });

        return response()->json($devices);
    }

    /**
     * Update notification preferences (system-wide for all admins).
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateNotificationPreferences(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'email_notifications_enabled' => 'sometimes|boolean',
            'push_notifications_enabled' => 'sometimes|boolean',
        ]);

        $settings = AlertSettings::current();
        $settings->update($validated);

        $this->addSystemLog('INFO', "System-wide notification preferences updated by {$user->name}");

        return response()->json([
            'success' => true,
            'message' => 'Notification preferences updated successfully (system-wide)',
            'preferences' => [
                'email_notifications_enabled' => $settings->email_notifications_enabled,
                'push_notifications_enabled' => $settings->push_notifications_enabled,
            ]
        ]);
    }
}

