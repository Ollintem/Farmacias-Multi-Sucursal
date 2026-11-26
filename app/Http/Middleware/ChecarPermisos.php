<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ChecarPermisos
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permiso, string $modulo): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(403, 'No tienes permiso para acceder a este módulo.');
        }

        // SuperAdmin tiene acceso total, sin importar la configuración de permisos.
        // Se usa el método del modelo para centralizar la lógica (case-insensitive y con fallback si la relación no está cargada).
        if (method_exists($user, 'esSuperAdmin') && $user->esSuperAdmin()) {
            return $next($request);
        }

        if ($user->permisosHabilitados($permiso, $modulo)) {
            return $next($request);
        }

        abort(403, 'No tienes permiso para acceder a este módulo.');
    }
}
