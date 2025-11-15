<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class MarkUserOnline
{
    /**
     * Tag the authenticated user as online for a short TTL.
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            Cache::put('user-online-'.Auth::id(), true, now()->addMinutes(5));
        }
        return $next($request);
    }
}
