<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Middleware AdminOnly
 *
 * Restringe acceso a rutas solo a usuarios con rol "admin" o "super_admin".
 * - Si no hay usuario autenticado o el rol no es válido, responde 404 (opcional 403).
 * - Normaliza el rol (minúsculas y sin guiones bajos) para comparar.
 */
class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        $u = $request->user();
        if (!$u) {
            abort(404); // Alternativa: abort(403) para "Prohibido"
        }

        // Normaliza el rol a minúsculas sin guiones bajos
        $role = strtolower(str_replace('_', '', $u->role));

        // Acepta admin y superadmin (con y sin guion bajo)
        if (!in_array($role, ['admin', 'superadmin'])) {
            abort(404); // Alternativa: abort(403)
        }

        return $next($request);
    }
}
