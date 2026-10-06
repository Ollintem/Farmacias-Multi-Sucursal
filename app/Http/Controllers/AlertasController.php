<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use App\Support\AlertasFeed;
use App\Support\AlertasResumen;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AlertasController extends Controller
{
    /**
     * Muestra el centro de notificaciones estilo Facebook: un solo listado
     * con traspasos, caducidades y ventas, nuevos primero.
     *
     * Entrada: query `sucursal`, `filtro` (todas|no_leidas|traspasos|caducidades|ventas).
     * Acepta `seccion` y `nivel` viejos y los traduce al filtro nuevo.
     * Salida: resources/views/pages/alertas/index.blade.php.
     */
    public function index(Request $request): View
    {
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        $selectedSucursalId = (int) (session('active_sucursal_id') ?? $request->query('sucursal') ?? $request->user()?->id_sucursal ?? $sucursales->first()?->id ?? 0);
        $selectedSucursal = $sucursales->firstWhere('id', $selectedSucursalId) ?? $sucursales->first();

        $filtro = strtolower(trim((string) $request->query('filtro', '')));

        if ($filtro === '') {
            $seccionVieja = strtolower(trim((string) $request->query('seccion', '')));
            $filtro = match ($seccionVieja) {
                'traspasos' => 'traspasos',
                'caducidades' => 'caducidades',
                'ventas' => 'ventas',
                default => 'todas',
            };
        }

        $filtro = in_array($filtro, ['todas', 'no_leidas', 'traspasos', 'caducidades', 'ventas'], true) ? $filtro : 'todas';

        $sucursalId = $selectedSucursal?->id;
        $usuarioId = $request->user()?->id;
        $avisos = $sucursalId ? AlertasFeed::items($sucursalId) : [];
        $leidas = $sucursalId ? AlertasFeed::leidas($sucursalId, $usuarioId) : [];

        $avisos = array_map(function (array $aviso) use ($leidas) {
            $aviso['leida'] = in_array($aviso['id'], $leidas, true);

            return $aviso;
        }, $avisos);

        $noLeidas = collect($avisos)->where('leida', false)->count();

        $notificaciones = match ($filtro) {
            'no_leidas' => array_values(array_filter($avisos, fn (array $a) => ! $a['leida'])),
            'traspasos', 'caducidades', 'ventas' => array_values(array_filter($avisos, fn (array $a) => $a['tipo'] === $filtro)),
            default => $avisos,
        };

        $conteo = $sucursalId ? AlertasResumen::counts($sucursalId) : ['pendientes' => 0, 'rojos' => 0, 'total' => 0];
        $tipos = [
            'traspasos' => collect($avisos)->where('tipo', 'traspasos')->count(),
            'caducidades' => collect($avisos)->where('tipo', 'caducidades')->count(),
            'ventas' => collect($avisos)->where('tipo', 'ventas')->count(),
        ];

        return view('pages.alertas.index', [
            'sucursales' => $sucursales,
            'selectedSucursal' => $selectedSucursal,
            'filtro' => $filtro,
            'notificaciones' => $notificaciones,
            'totalAvisos' => count($avisos),
            'totalNoLeidas' => $noLeidas,
            'tipos' => $tipos,
            'conteo' => $conteo,
        ]);
    }

    /**
     * Devuelve las últimas notificaciones para la campana (polling cada 30 s).
     */
    public function feed(Request $request): JsonResponse
    {
        $sucursalId = (int) (session('active_sucursal_id') ?? $request->user()?->id_sucursal ?? 0);

        if (! $sucursalId || ! ($request->user()?->puedeVerModulo('Alertas') ?? false)) {
            return response()->json(['total' => 0, 'no_leidas' => 0, 'avisos' => []]);
        }

        $avisos = AlertasFeed::items($sucursalId);
        $leidas = AlertasFeed::leidas($sucursalId, $request->user()?->id);

        $lista = collect($avisos)
            ->take(10)
            ->map(function (array $aviso) use ($leidas) {
                return [
                    'id' => $aviso['id'],
                    'tipo' => $aviso['tipo'],
                    'titulo' => $aviso['titulo'],
                    'detalle' => $aviso['detalle'],
                    'url' => $aviso['url'],
                    'leida' => in_array($aviso['id'], $leidas, true),
                    'tiempo' => $aviso['fecha']?->diffForHumans() ?? 'reciente',
                    'icono' => $aviso['icono'],
                    'prioridad' => $aviso['prioridad'],
                ];
            })
            ->values();

        return response()->json([
            'total' => count($avisos),
            'no_leidas' => collect($avisos)->reject(fn (array $a) => in_array($a['id'], $leidas, true))->count(),
            'ver_todas' => route('alertas.index', ['sucursal' => $sucursalId]),
            'avisos' => $lista,
        ]);
    }

    /**
     * Marca un solo aviso como leído desde el menú ••• de cada fila.
     */
    public function marcarLeida(Request $request): RedirectResponse
    {
        $sucursalId = (int) (session('active_sucursal_id') ?? $request->user()?->id_sucursal ?? 0);
        $id = trim((string) $request->input('id', ''));

        if ($sucursalId && $id !== '') {
            AlertasFeed::marcarUna($sucursalId, $id, $request->user()?->id);
        }

        return back();
    }

    /**
     * Marca todos los avisos actuales como leídos (la campana deja de contarlos).
     */
    public function marcarLeidas(Request $request): RedirectResponse|JsonResponse
    {
        $sucursalId = (int) (session('active_sucursal_id') ?? $request->user()?->id_sucursal ?? 0);

        if ($sucursalId) {
            $ids = collect(AlertasFeed::items($sucursalId))->pluck('id')->all();
            AlertasFeed::marcarTodas($sucursalId, $ids, $request->user()?->id);
        }

        if ($request->expectsJson()) {
            return response()->json(['no_leidas' => 0]);
        }

        return back()->with('success', 'Todas las notificaciones marcadas como leídas.');
    }
}
