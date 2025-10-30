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
use Illuminate\View\View;

class ProfileController extends Controller
{
    /** (Opcional) Vista dedicada de perfil */
    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    /** Actualiza nombre y email juntos (si usas la vista dedicada) */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::to($this->targetUrl($request))
            ->with('status', 'profile-updated')
            ->with('account_tab', $request->input('tab', 'username'));
    }

    /** Tab: Username */
    public function updateUsername(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required','string','max:255'],
        ]);

        $request->user()->update(['name' => $validated['name']]);

        return Redirect::to($this->targetUrl($request))
            ->with('status', 'username-updated')
            ->with('account_tab', 'username');
    }

    /** Tab: Email */
    public function updateEmail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => [
                'required','string','email','max:255',
                Rule::unique('users','email')->ignore($request->user()->id),
            ],
        ]);

        $u = $request->user();
        $u->email = strtolower($validated['email']);
        if ($u->isDirty('email')) {
            $u->email_verified_at = null;
        }
        $u->save();

        return Redirect::to($this->targetUrl($request))
            ->with('status', 'email-updated')
            ->with('account_tab', 'email');
    }

    /** Tab: Password */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required','current_password'],
            'password'         => ['required', Password::min(8)->uncompromised(), 'confirmed'],
        ]);

        $u = $request->user();
        $u->password = Hash::make($request->password);
        $u->save();

        return Redirect::to($this->targetUrl($request))
            ->with('status', 'password-updated')
            ->with('account_tab', 'password');
    }

    /** Danger: Delete account (pide contraseña actual) */
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
