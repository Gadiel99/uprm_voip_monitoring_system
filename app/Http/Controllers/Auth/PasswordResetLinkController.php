<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\PasswordResetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    protected $passwordResetService;

    public function __construct(PasswordResetService $passwordResetService)
    {
        $this->passwordResetService = $passwordResetService;
    }

    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Log the password reset attempt
        \App\Helpers\SystemLogger::logInfo(
            'Password reset link requested',
            ['email' => $request->email, 'ip' => $request->ip()]
        );

        // Use enhanced password reset service with explicit token invalidation
        $status = $this->passwordResetService->sendResetLinkWithInvalidation($request->email);

        // Validate that token invalidation worked correctly
        $validation = $this->passwordResetService->validateTokenUniqueness($request->email);
        
        if (!$validation['is_unique']) {
            \App\Helpers\SystemLogger::logError(
                'Token invalidation failed - multiple tokens exist',
                [
                    'email' => $request->email,
                    'token_count' => $validation['token_count'],
                    'tokens' => $validation['tokens']
                ]
            );
        } else {
            \App\Helpers\SystemLogger::logInfo(
                'Token invalidation successful - only one token exists',
                ['email' => $request->email]
            );
        }

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
}
