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
     * Muestra el centro de alertas: filas leídas desde el modelo Alerta.
     *
     * Cada alerta trae su grupo (stock|caducidad|traspasos|ventas) según su
     * tipo_alerta y si el usuario ya la leyó según alertas_usuarios
     * (estado LEIDA o fecha_leido).
     *
     * Entrada: query `sucursal`, `filtro` (todas|no_leidas|stock|caducidad|traspasos|ventas).
     * Acepta `seccion` y los nombres viejos (caducidades) como alias.
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
                'caducidades', 'caducidad' => 'caducidad',
                'stock' => 'stock',
                'ventas' => 'ventas',
                default => 'todas',
            };
        }

        $filtro = match ($filtro) {
            'caducidades' => 'caducidad',
            'traspaso' => 'traspasos',
            default => $filtro,
        };

        $filtro = in_array($filtro, ['todas', 'no_leidas', 'stock', 'caducidad', 'traspasos', 'ventas'], true) ? $filtro : 'todas';

        $sucursalId = $selectedSucursal?->id;
        $usuarioId = $request->user()?->id;
        $avisos = $sucursalId ? AlertasFeed::listado($sucursalId, $usuarioId) : [];

        $noLeidas = collect($avisos)->where('leida', false)->count();

        $notificaciones = match ($filtro) {
            'no_leidas' => array_values(array_filter($avisos, fn (array $a) => ! $a['leida'])),
            'stock', 'caducidad', 'traspasos', 'ventas' => array_values(array_filter($avisos, fn (array $a) => $a['grupo'] === $filtro)),
            default => $avisos,
        };

        $conteo = $sucursalId ? AlertasResumen::counts($sucursalId) : ['pendientes' => 0, 'rojos' => 0, 'total' => 0];
        $tipos = [
            'stock' => collect($avisos)->where('grupo', 'stock')->count(),
            'caducidad' => collect($avisos)->where('grupo', 'caducidad')->count(),
            'traspasos' => collect($avisos)->where('grupo', 'traspasos')->count(),
            'ventas' => collect($avisos)->where('grupo', 'ventas')->count(),
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
     * Devuelve las últimas alertas para la campana (polling cada 30 s).
     */
    public function feed(Request $request): JsonResponse
    {
        $sucursalId = (int) (session('active_sucursal_id') ?? $request->user()?->id_sucursal ?? 0);

        if (! $sucursalId || ! ($request->user()?->puedeVerModulo('Alertas') ?? false)) {
            return response()->json(['total' => 0, 'no_leidas' => 0, 'avisos' => []]);
        }

        $avisos = AlertasFeed::listado($sucursalId, $request->user()?->id);

        $lista = collect($avisos)
            ->take(10)
            ->map(fn (array $aviso) => [
                'id' => $aviso['id'],
                'alerta_id' => $aviso['alerta_id'],
                'tipo' => $aviso['tipo'],
                'grupo' => $aviso['grupo'],
                'subtipo' => $aviso['subtipo'],
                'titulo' => $aviso['titulo'],
                'detalle' => $aviso['detalle'],
                'url' => $aviso['url'],
                'leida' => $aviso['leida'],
                'tiempo' => $aviso['fecha']?->diffForHumans() ?? 'reciente',
                'icono' => $aviso['icono'],
                'prioridad' => $aviso['prioridad'],
            ])
            ->values();

        return response()->json([
            'total' => count($avisos),
            'no_leidas' => collect($avisos)->where('leida', false)->count(),
            'ver_todas' => route('alertas.index', ['sucursal' => $sucursalId]),
            'avisos' => $lista,
        ]);
    }

    /**
     * Marca una sola alerta como leída desde el menú ••• de cada fila.
     *
     * Acepta el id numérico de la alerta o el id estable anterior
     * ("traspaso:5", "caducidad:3").
     */
    public function marcarLeida(Request $request): RedirectResponse
    {
        $sucursalId = (int) (session('active_sucursal_id') ?? $request->user()?->id_sucursal ?? 0);
        $id = trim((string) $request->input('id', ''));

        if ($sucursalId && $id !== '') {
            if (ctype_digit($id)) {
                AlertasFeed::marcarLeidaPorAlerta((int) $id, $request->user()?->id);
            } else {
                AlertasFeed::marcarUna($sucursalId, $id, $request->user()?->id);
            }
        }

        return back();
    }

    /**
     * Marca todas las alertas actuales como leídas (la campana deja de contarlas).
     */
    public function marcarLeidas(Request $request): RedirectResponse|JsonResponse
    {
        $sucursalId = (int) (session('active_sucursal_id') ?? $request->user()?->id_sucursal ?? 0);

        if ($sucursalId) {
            foreach (AlertasFeed::listado($sucursalId, $request->user()?->id) as $aviso) {
                AlertasFeed::marcarLeidaPorAlerta($aviso['alerta_id'], $request->user()?->id);
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['no_leidas' => 0]);
        }

        return back()->with('success', 'Todas las notificaciones marcadas como leídas.');
    }
}
