<?php

namespace App\Http\Middleware;

use App\Models\Sucursal;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetActiveSucursal
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user) {
            $sucursalId = $request->query('sucursal');

            // Solo una sucursal activa puede elegirse explícitamente: una
            // inactiva o inexistente se ignora y se conserva la actual. La
            // sucursal ya activa en sesión no se revoca (no interrumpe
            // operaciones en curso) y desactivar nunca toca ventas ni historial.
            if ($sucursalId !== null && ! Sucursal::whereKey($sucursalId)->where('es_activa', true)->exists()) {
                $sucursalId = null;
            }

            $sucursalId ??= $request->session()->get('active_sucursal_id')
                ?? $user->id_sucursal
                ?? Sucursal::where('es_activa', true)->orderBy('nombre_sucursal')->first()?->id;

            if ($sucursalId) {
                $request->session()->put('active_sucursal_id', $sucursalId);
            }
        }

        return $next($request);
    }
}
