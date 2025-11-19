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
        // If trying to access authenticated routes without being logged in, redirect to login
        if (!Auth::check() && !$request->is('login', 'register', 'forgot-password', 'reset-password/*')) {
            return redirect()->route('login')->with('status', 'Please login to continue.');
        }
        
        $response = $next($request);
        
        // Prevent caching for authenticated pages
        if (Auth::check()) {
            return $response->header('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0')
                            ->header('Pragma', 'no-cache')
                            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
        }
        
        return $response;
    }
}
