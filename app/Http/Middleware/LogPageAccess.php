<?php

namespace App\Http\Middleware;

use App\Helpers\SystemLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * LogPageAccess Middleware
 * 
 * Logs every page access with user information and timestamp
 */
class LogPageAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the route name or path
        $routeName = $request->route()?->getName() ?? $request->path();
        $method = $request->method();
        
        // Only log GET requests for page views (not POST, PUT, DELETE)
        if ($method === 'GET') {
            SystemLogger::logPageAccess($routeName);
        }
        
        return $next($request);
    }
}
