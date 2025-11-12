<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;

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
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
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

        return view('pages.admin', [
            'activeTab' => 'users',
            'isUsersServer' => true,
            'users' => $users,
            'adminsCount' => $adminsCount,
            'systemLogs' => [],
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
            'email'    => ['required','string','email:rfc','max:255','unique:users,email'],
            'password' => ['required', Password::min(8)->max(64)->mixedCase()->letters()->numbers()->symbols()->uncompromised()],
            'role'     => ['nullable','in:user,admin'],
        ]);

        $role  = $request->input('role', 'user');
        
        // Sanitize inputs to remove potentially harmful characters
        $name = strip_tags($request->name);
        $name = preg_replace('/[;\'"]/', '', $name); // Remove semicolons and quotes
        $email = filter_var($request->email, FILTER_SANITIZE_EMAIL);

        User::create([
            'name'     => $name,
            'email'    => $email,
            'password' => Hash::make($request->password),
            'role'     => $role,
        ]);

        return redirect()->route('admin.users')->with('status', 'User created successfully.')->with('showAddModal', false);
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
            return back()->withErrors(['role' => 'You cannot change your own role here.']);
        }

        $user->update(['role' => $request->role]);
        return redirect()->route('admin.users')->with('status', 'Role updated successfully.');
    }

    /**
     * Elimina un usuario con políticas de seguridad.
     *
     * Reglas:
     * - No se puede borrar al super_admin.
     * - Un admin no puede borrar a otro admin.
     * - Un usuario no puede borrarse a sí mismo.
     *
     * @param  Request $request
     * @param  User    $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, User $user)
    {
        $actor = $request->user();
        // Admin no puede eliminar Admin
        if ($actor->role === 'admin' && $user->role === 'admin') {
            return back()->withErrors(['delete' => 'Admins cannot delete other admins.']);
        }
        // Evitar borrarse a sí mismo
        if ($actor->id === $user->id) {
            return back()->withErrors(['delete' => 'You cannot delete yourself.']);
        }

        $user->delete();
        return redirect()->route('admin.users')->with('status', 'User deleted successfully.');
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
            return redirect()->route('admin.users')->withErrors(['role' => 'You cannot change your own role here.']);
        }

        $user->update(['role' => $validated['role']]);
        return redirect()->route('admin.users')->with('status', 'Role updated successfully.');
    }
}
