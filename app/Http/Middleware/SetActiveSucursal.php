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
            $sucursalId = $request->query('sucursal')
                ?? $request->session()->get('active_sucursal_id')
                ?? $user->id_sucursal
                ?? Sucursal::orderBy('nombre_sucursal')->first()?->id;

            if ($sucursalId) {
                $request->session()->put('active_sucursal_id', $sucursalId);
            }
        }

        return $next($request);
    }
}
