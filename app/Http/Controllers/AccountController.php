<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controlador de Cuenta del usuario (perfil básico).
 *
 * - Muestra la vista de ajustes (nombre, email, password).
 * - Actualiza datos del usuario autenticado con validación.
 */
class AccountController extends Controller
{
    /**
     * Muestra el formulario de configuración de cuenta.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function showSettings()
    {
        $user = auth()->user();
        return view('account.settings', compact('user'));
    }

    /**
     * Actualiza los ajustes de cuenta del usuario.
     *
     * Validación:
     * - name/email requeridos (email único ignorando el propio id).
     * - password opcional, min:8, confirmado.
     * Lógica:
     * - Solo cambia la contraseña si se provee.
     *
     * @param  Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSettings(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = $validated['password'];
        }

        $user->updateProfile($data);

        return back()->with('success', 'Account updated successfully.');
    }
}
