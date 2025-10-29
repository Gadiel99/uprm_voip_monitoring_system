<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    // Lista de usuarios y formulario de creación
    public function index()
    {
        $users = \App\Models\User::orderByRaw("FIELD(role,'superadmin','super_admin','admin','user')")
            ->orderBy('name')
            ->get();

        $adminsCount = $users->where('role', 'admin')->count();
        $superAdminsCount = $users
            ->filter(fn($u) => in_array(strtolower(str_replace('_','',$u->role)), ['superadmin']))
            ->count();

        return view('pages.admin', [
            'activeTab' => 'users',
            'users' => $users,
            'adminsCount' => $adminsCount,
            'superAdminsCount' => $superAdminsCount,
        ]);
    }



    // Crear usuario
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

        return back()->with('status', 'User created.');
    }

    // Cambiar rol (solo super_admin)
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
        return back()->with('status', 'Role updated.');
    }

    // Eliminar usuario
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
        return back()->with('status', 'User deleted.');
    }
}
