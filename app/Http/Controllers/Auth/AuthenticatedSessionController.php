<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Helpers\SystemLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        try {
            $email = $request->input('email');
            $request->authenticate();
            
            $request->session()->regenerate();
            
            // Log successful login
            SystemLogger::logLoginAttempt($email, true);
            
            return redirect()->intended(route('dashboard', absolute: false));
        } catch (\Exception $e) {
            // Log failed login attempt
            $email = $request->input('email', 'unknown');
            SystemLogger::logLoginAttempt($email, false);
            throw $e;
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Log logout before destroying session
        if (Auth::check()) {
            SystemLogger::logLogout(Auth::user()->email);
        }
        
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
