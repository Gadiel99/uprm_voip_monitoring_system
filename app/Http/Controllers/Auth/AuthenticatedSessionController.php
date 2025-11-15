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
        $email = $request->input('email');
        
        try {
            $request->authenticate();
            
            $request->session()->regenerate();
            
            // Log successful login with context
            SystemLogger::logLoginAttempt($email, true, [
                'user_agent' => $request->userAgent(),
                'intended_url' => $request->session()->get('url.intended')
            ]);
            
            return redirect()->intended(route('dashboard', absolute: false));
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Log failed login attempt with reason
            SystemLogger::logLoginAttempt($email, false, [
                'reason' => 'Invalid credentials (wrong email/password)',
                'user_agent' => $request->userAgent(),
                'errors' => $e->errors()
            ]);
            throw $e;
        } catch (\Exception $e) {
            // Log other errors (database, etc)
            SystemLogger::logLoginAttempt($email, false, [
                'reason' => 'System error: ' . $e->getMessage(),
                'user_agent' => $request->userAgent(),
                'exception' => get_class($e)
            ]);
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
