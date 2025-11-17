<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

/**
 * Controlador de Perfil del usuario autenticado.
 *
 * - Edit: muestra la vista dedicada (opcional).
 * - Update: actualiza nombre/email (marca email como no verificado si cambia).
 * - updateUsername/updateEmail/updatePassword: flujos por pestaña (modal).
 * - destroy: elimina la cuenta previa confirmación de contraseña.
 * - targetUrl: retorna a la URL/anchor deseado (misma página + modal abierto).
 */
class ProfileController extends Controller
{
    /**
     * Vista de edición de perfil (opcional).
     *
     * @param  Request $request
     * @return View
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    /**
     * Actualiza nombre y correo (vista dedicada).
     *
     * @param  ProfileUpdateRequest $request
     * @return RedirectResponse
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::to($this->targetUrl($request))
            ->with('account_status', 'profile-updated')
            ->with('account_tab', $request->input('tab', 'username'));
    }

    /**
     * Pestaña: Username (actualiza solo el nombre).
     *
     * @param  Request $request
     * @return RedirectResponse
     */
    public function updateUsername(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required','string','min:2','max:255','regex:/\S/'],
        ]);
        if ($validator->fails()) {
            return Redirect::back()
                ->withErrors($validator)
                ->withInput()
                ->with('account_tab', 'username');
        }
        $validated = $validator->validated();
        $clean = trim($validated['name']);
        $request->user()->update(['name' => $clean]);

        return Redirect::to($this->targetUrl($request))
            ->with('account_status', 'name-updated')
            ->with('account_tab', 'username');
    }

    /**
     * Pestaña: Email (actualiza solo el correo).
     *
     * @param  Request $request
     * @return RedirectResponse
     */
    public function updateEmail(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => [
                'required','string','email:rfc','max:255',
                Rule::unique('users','email')->ignore($request->user()->id),
            ],
        ]);
        if ($validator->fails()) {
            return Redirect::back()
                ->withErrors($validator)
                ->withInput()
                ->with('account_tab', 'email');
        }
        $validated = $validator->validated();
        $u = $request->user();
        $u->email = strtolower(trim($validated['email']));
        if ($u->isDirty('email')) {
            $u->email_verified_at = null;
        }
        $u->save();

        return Redirect::to($this->targetUrl($request))
            ->with('account_status', 'email-updated')
            ->with('account_tab', 'email');
    }

    /**
     * Pestaña: Password (requiere contraseña actual + confirmación).
     *
     * @param  Request $request
     * @return RedirectResponse
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $u = $request->user();
        $validator = Validator::make($request->all(), [
            'current_password' => ['required','current_password'],
            'password' => [
                'required','string',
                Password::min(8)->max(64)->mixedCase()->letters()->numbers()->symbols()->uncompromised(),
                'confirmed',
                function($attribute,$value,$fail) use ($u) {
                    if (Hash::check($value, $u->password)) {
                        $fail('The new password must be different from the current password.');
                    }
                },
            ],
        ]);
        if ($validator->fails()) {
            return Redirect::back()
                ->withErrors($validator)
                ->withInput()
                ->with('account_tab', 'password');
        }
        $validated = $validator->validated();
        $u->password = Hash::make($validated['password']);
        $u->save();

        return Redirect::to($this->targetUrl($request))
            ->with('account_status', 'password-updated')
            ->with('account_tab', 'password');
    }

    /**
     * Elimina la cuenta del usuario (requiere contraseña actual).
     *
     * Pasos:
     * - Valida contraseña con bag 'userDeletion'.
     * - Cierra sesión, elimina usuario, invalida sesión y tokens.
     *
     * @param  Request $request
     * @return RedirectResponse
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required','current_password'],
        ]);

        $user = $request->user();
        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Determina a qué URL regresar después de actualizar (misma página/modal).
     *
     * Reglas:
     * - Usa 'return_to' si es relativo o del mismo host.
     * - Usa Referer si coincide host.
     * - Fallback: dashboard.
     *
     * @param  Request $request
     * @return string
     */
    private function targetUrl(Request $request): string
    {
        $host = parse_url(config('app.url') ?: url('/'), PHP_URL_HOST);

        $to = $request->input('return_to');
        if ($to && (Str::startsWith($to, ['/']) || Str::startsWith($to, [url('/')]))) {
            return $to;
        }

        $referer = $request->headers->get('referer');
        if ($referer && (!parse_url($referer, PHP_URL_HOST) || parse_url($referer, PHP_URL_HOST) === $host)) {
            return $referer;
        }

        return route('dashboard');
    }
}
