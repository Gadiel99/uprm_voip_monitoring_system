<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        $u = $request->user();
        if (!$u) {
            abort(404); // o 403 si no quieres "stealth"
        }

        // normaliza el rol a minúsculas sin guiones bajos
        $role = strtolower(str_replace('_', '', $u->role));

        // acepta admin y superadmin (con y sin guion bajo)
        if (!in_array($role, ['admin', 'superadmin'])) {
            abort(404); // o 403
        }

        return $next($request);
    }
}
