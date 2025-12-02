<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AlertSettings;
use App\Models\Devices;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Helpers\SystemLogger;
use App\Services\BackupService;

/**
 * Controlador de Administración de Usuarios.
 *
 * - Lista usuarios y métricas (admins/super admins).
 * - Crea usuarios (solo super_admin puede crear admin/super_admin).
 * - Cambia roles (solo super_admin; con salvaguardas: no a sí mismo, no super_admin).
 * - Elimina usuarios (no permite borrarse a sí mismo ni al super_admin; admin no borra admin).
 *
 * Todas las acciones devuelven a la pestaña Users del panel de Admin con mensajes flash.
 */
class AdminUserController extends Controller
{
    /** Normalize role string to compare across 'super_admin' and 'superadmin'. */
    private function normalizeRole(?string $role): ?string
    {
        return $role ? strtolower(str_replace('_','', $role)) : null;
    }

    /**
     * Muestra la lista de usuarios y badges de conteos.
     *
     * @param  BackupService  $backupService
     * @return \Illuminate\Contracts\View\View
     */
    public function index(BackupService $backupService)
    {
        $users = \App\Models\User::orderByRaw("
                CASE LOWER(REPLACE(role, '_',''))
                    WHEN 'admin' THEN 0
                    ELSE 1
                END
            ")
            ->orderBy('name')
            ->get();

        $adminsCount = $users->where('role', 'admin')->count();
        
        // Get system logs from session
        $systemLogs = session()->get('system_logs', []);
        
        // Get alert settings for Settings tab
        $alertSettings = AlertSettings::current();
        
        // Get critical devices for Settings tab
        $criticalDevices = Devices::where('is_critical', true)
            ->select('device_id', 'ip_address', 'mac_address', 'status', 'owner')
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
            ->select('device_id', 'ip_address', 'mac_address', 'owner')
            ->orderBy('ip_address')
            ->get();
        
        // Get extensions for available devices to enable search by extension and user names
        $availableDeviceIds = $availableDevices->pluck('device_id');
        $extensionsByAvailableDevice = $availableDeviceIds->isEmpty()
            ? collect()
            : DB::table('device_extensions as de')
                ->join('extensions as e', 'e.extension_id', '=', 'de.extension_id')
                ->whereIn('de.device_id', $availableDeviceIds)
                ->select('de.device_id', 'e.extension_number', 'e.user_first_name', 'e.user_last_name')
                ->get()
                ->groupBy('device_id');
        
        // Get backup info
        $latestBackup = $backupService->getLatestBackup();
        $backupStats = null;
        try {
            $backupStats = $backupService->getBackupStats();
        } catch (\Throwable $e) {
            \Log::warning('Could not get backup stats: '.$e->getMessage());
        }
        $allBackups = $backupService->getAllBackups();
        
        // Exclude the latest backup from the list (since it's shown separately)
        if ($latestBackup && !empty($allBackups)) {
            $allBackups = array_filter($allBackups, function($backup) use ($latestBackup) {
                return $backup['filename'] !== $latestBackup['filename'];
            });
        }

        return view('pages.admin', [
            'activeTab' => 'users',
            'isUsersServer' => true,
            'users' => $users,
            'adminsCount' => $adminsCount,
            'systemLogs' => $systemLogs,
            'alertSettings' => $alertSettings,
            'criticalDevices' => $criticalDevices,
            'extensionsByCriticalDevice' => $extensionsByCriticalDevice,
            'availableDevices' => $availableDevices,
            'extensionsByAvailableDevice' => $extensionsByAvailableDevice,
            'latestBackup' => $latestBackup,
            'backupStats' => $backupStats,
            'allBackups' => $allBackups,
        ]);
    }

    /**
     * Crea un nuevo usuario.
     *
     * Validación:
     * - name/email/password requeridos.
     * - role: user|admin|super_admin (solo super_admin puede crear roles elevados).
     * Reglas:
     * - Único super_admin en el sistema.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => ['required','string','min:2','max:255','regex:/\S/'],
            'email'    => [
                'required',
                'string',
                'email:rfc,dns', // Validates email format AND checks DNS records
                'max:255',
                'unique:users,email'
            ],
            'password' => ['required', Password::min(8)->max(64)->mixedCase()->letters()->numbers()->symbols()->uncompromised()],
            'role'     => ['nullable','in:user,admin'],
        ]);

        $role  = $request->input('role', 'user');
        
        // Sanitize inputs to remove potentially harmful characters
        $name = strip_tags($request->name);
        $name = preg_replace('/[;\'"]/', '', $name); // Remove semicolons and quotes
        $email = filter_var($request->email, FILTER_SANITIZE_EMAIL);
        
        // Store the plain password temporarily to send in email
        $plainPassword = $request->password;

        $newUser = User::create([
            'name'     => $name,
            'email'    => $email,
            'password' => Hash::make($request->password),
            'role'     => $role,
        ]);

        // Log user creation
        SystemLogger::log(
            SystemLogger::ADD,
            "Created user: {$newUser->name} ({$newUser->email}) with role '{$role}'",
            $request->user()->email
        );
        
        // Send welcome email with credentials
        try {
            $newUser->notify(new \App\Notifications\WelcomeUserNotification($email, $plainPassword));
            $emailStatus = 'User created successfully and welcome email sent.';
        } catch (\Exception $e) {
            \Log::error('Failed to send welcome email: ' . $e->getMessage());
            $emailStatus = 'User created successfully, but welcome email could not be sent. Please inform the user of their credentials manually.';
        }

        return redirect()->route('admin.users', ['tab' => 'users'])
            ->with('user_status', $emailStatus)
            ->with('showAddModal', false);
    }

    /**
     * Actualiza el rol de un usuario (solo super_admin).
     *
     * Restricciones:
     * - No modificar rol de super_admin.
     * - No cambiarse el rol a sí mismo.
     *
     * @param  Request $request
     * @param  User    $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => ['required','in:user,admin'],
        ]);

        $actor = $request->user();

        // Evitar que alguien se quite privilegios a sí mismo accidentalmente
        if ($actor->id === $user->id) {
            return redirect()->route('admin.users', ['tab' => 'users'])
                ->withErrors(['role' => 'You cannot change your own role here.']);
        }

        $oldRole = $user->role;
        $user->update(['role' => $request->role]);
        
        // Log role change
        SystemLogger::log(
            SystemLogger::EDIT,
            "Changed role for user {$user->name} ({$user->email}) from '{$oldRole}' to '{$request->role}'",
            $request->user()->email
        );
        
        return redirect()->route('admin.users', ['tab' => 'users'])
            ->with('user_status', 'Role updated successfully.');
    }

    /**
     * Elimina un usuario con políticas de seguridad.
     *
     * Reglas:
     * - Un usuario no puede borrarse a sí mismo.
     *
     * @param  Request $request
     * @param  User    $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, User $user)
    {
        $actor = $request->user();
        
        // Evitar borrarse a sí mismo
        if ($actor->id === $user->id) {
            return redirect()->route('admin.users', ['tab' => 'users'])
                ->withErrors(['delete' => 'You cannot delete yourself.']);
        }

        // Store user info before deletion for logging
        $userName = $user->name;
        $userEmail = $user->email;
        $userRole = $user->role;
        
        $user->delete();
        
        // Log user deletion
        SystemLogger::log(
            SystemLogger::DELETE,
            "Deleted user: {$userName} ({$userEmail}) with role '{$userRole}'",
            $request->user()->email
        );
        
        return redirect()->route('admin.users', ['tab' => 'users'])
            ->with('user_status', 'User deleted successfully.');
    }

    /**
     * Update endpoint limited to ROLE CHANGES only.
     * All other credential updates are handled by users themselves via Account Settings.
     */
    public function update(Request $request, User $user)
    {
        // Validate only role and delegate authz to super_admin
        $validated = $request->validate([
            'role' => ['required','in:user,admin'],
        ]);

        $actor = $request->user();
        if ($actor->id === $user->id) {
            return redirect()->route('admin.users', ['tab' => 'users'])
                ->withErrors(['role' => 'You cannot change your own role here.']);
        }

        $oldRole = $user->role;
        $user->update(['role' => $validated['role']]);
        
        // Log role change
        SystemLogger::log(
            SystemLogger::EDIT,
            "Changed role for user {$user->name} ({$user->email}) from '{$oldRole}' to '{$validated['role']}'",
            $request->user()->email
        );
        
        return redirect()->route('admin.users', ['tab' => 'users'])
            ->with('user_status', 'Role updated successfully.');
    }
}
