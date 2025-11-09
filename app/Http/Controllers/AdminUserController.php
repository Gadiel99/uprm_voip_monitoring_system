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
    /**
     * Muestra la lista de usuarios y badges de conteos.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $users = \App\Models\User::orderByRaw("
                CASE LOWER(REPLACE(role, '_',''))
                    WHEN 'superadmin' THEN 0
                    WHEN 'admin' THEN 1
                    ELSE 2
                END
            ")
            ->orderBy('name')
            ->get();

        $adminsCount = $users->where('role', 'admin')->count();
        $superAdminsCount = $users
            ->filter(fn($u) => in_array(strtolower(str_replace('_','',$u->role)), ['superadmin']))
            ->count();

        return view('pages.admin', [
            'activeTab' => 'users',
            'isUsersServer' => true,
            'users' => $users,
            'adminsCount' => $adminsCount,
            'superAdminsCount' => $superAdminsCount,
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
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255','unique:users,email'],
            'password' => ['required', Password::min(8)],
            'role'     => ['nullable','in:user,admin,super_admin'],
        ]);

        $actor = $request->user();
        $role  = $request->input('role', 'user');

        // Solo el super_admin puede crear admins/super_admins
        if ($role !== 'user' && $actor->role !== 'super_admin') {
            return back()->withErrors(['role' => 'Only super admin can create admins.'])->withInput();
        }

        // Garantizar único super_admin
        if ($role === 'super_admin' && User::where('role', 'super_admin')->exists()) {
            return back()->withErrors(['role' => 'There is already a super admin.'])->withInput();
        }

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $role,
        ]);

        return redirect()->route('admin', ['tab' => 'users'])->with('status', 'User created.');
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
        if ($actor->role !== 'super_admin') {
            abort(403, 'Only super admin can change roles.');
        }

        // No permitir tocar super_admin
        if ($user->role === 'super_admin') {
            return back()->withErrors(['role' => 'Cannot modify super admin role.']);
        }

        // Evitar que alguien se quite privilegios a sí mismo accidentalmente
        if ($actor->id === $user->id) {
            return back()->withErrors(['role' => 'You cannot change your own role here.']);
        }

        $user->update(['role' => $request->role]);
        return redirect()->route('admin', ['tab' => 'users'])->with('status', 'Role updated.');
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

        // Solo admin/super_admin llegan aquí por middleware; ahora política fina:
        if ($user->role === 'super_admin') {
            return back()->withErrors(['delete' => 'Cannot delete the super admin.']);
        }
        // Admin no puede eliminar Admin
        if ($actor->role === 'admin' && $user->role === 'admin') {
            return back()->withErrors(['delete' => 'Admins cannot delete other admins.']);
        }
        // Evitar borrarse a sí mismo
        if ($actor->id === $user->id) {
            return back()->withErrors(['delete' => 'You cannot delete yourself.']);
        }

        $user->delete();
        return redirect()->route('admin', ['tab' => 'users'])->with('status', 'User deleted.');
    }

    /**
     * Actualiza datos básicos del usuario (nombre, email y password opcional).
     * Solo super_admin puede cambiar el rol desde este endpoint (si se envía).
     */
    public function update(Request $request, User $user)
    {
        $rules = [
            'name'  => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:users,email,'.$user->id],
            'password' => ['nullable', Password::min(8)],
        ];

        // Role only allowed for super_admin; if present, validate value
        if ($request->filled('role')) {
            $rules['role'] = ['in:user,admin,super_admin'];
        }

        $validated = $request->validate($rules);

        $payload = [
            'name'  => $validated['name'],
            'email' => $validated['email'],
        ];

        if (!empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        // Only super_admin can change roles and cannot demote/alter a super_admin arbitrarily
        if ($request->filled('role') && $request->user()->role === 'super_admin') {
            // Protect unique super_admin: avoid making two accidentally
            if (($validated['role'] ?? null) === 'super_admin' && User::where('role', 'super_admin')->where('id', '!=', $user->id)->exists()) {
                return redirect()->route('admin', ['tab' => 'users'])->withErrors(['role' => 'There is already a super admin.']);
            }
            $payload['role'] = $validated['role'];
        }

        $user->update($payload);

        return redirect()->route('admin', ['tab' => 'users'])->with('status', 'User updated.');
    }
}
