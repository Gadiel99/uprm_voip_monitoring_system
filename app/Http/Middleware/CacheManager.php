<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Middleware CacheManager
 *
 * Manages browser caching for authenticated pages.
 * This ensures that after logout, users cannot use the back button
 * to view cached versions of protected pages.
 * 
 * Also redirects unauthenticated users trying to access cached pages to login.
 */
class CacheManager
{
    public function handle(Request $request, Closure $next)
    {
        // Allow all authentication-related routes without redirect
        $authRoutes = [
            'login', 'register', 'logout',
            'forgot-password', 'reset-password', 'reset-password/*',
            'verify-email', 'verify-email/*', 'email/verification-notification',
            'confirm-password'
        ];
        
        // Check if current route matches any auth route patterns
        $isAuthRoute = false;
        foreach ($authRoutes as $route) {
            if ($request->is($route)) {
                $isAuthRoute = true;
                break;
            }
        }
        
        // If trying to access non-auth routes without being logged in, redirect to login
        if (!Auth::check() && !$isAuthRoute) {
            return redirect()->route('login')->with('status', 'Please login to continue.');
        }
        
        $response = $next($request);
        
        // Prevent caching for authenticated pages
        if (Auth::check()) {
            // BinaryFileResponse and StreamedResponse (file downloads) use headers differently
            if ($response instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse || 
                $response instanceof \Symfony\Component\HttpFoundation\StreamedResponse) {
                $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0');
                $response->headers->set('Pragma', 'no-cache');
                $response->headers->set('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
            } else {
                // Regular responses can use the chainable header method
                $response->header('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0')
                         ->header('Pragma', 'no-cache')
                         ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
            }
        }
        
        return $response;
    }
}
