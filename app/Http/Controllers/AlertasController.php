<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\Lote;
use App\Models\Sucursal;
use App\Models\Traspaso;
use App\Models\Venta;
use App\Support\EstadoCaducidad;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AlertasController extends Controller
{
    /**
     * Muestra la ventana de alertas con tres secciones: traspasos, caducidades y ventas del día.
     *
     * Entrada: query `sucursal`, `seccion` (traspasos|caducidades|ventas) y `nivel` (todos|rojo|amarillo|verde).
     * Salida: resources/views/pages/alertas/index.blade.php.
     */
    public function index(Request $request): View
    {
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        $selectedSucursalId = (int) (session('active_sucursal_id') ?? $request->query('sucursal') ?? $request->user()?->id_sucursal ?? $sucursales->first()?->id ?? 0);
        $selectedSucursal = $sucursales->firstWhere('id', $selectedSucursalId) ?? $sucursales->first();

        $seccion = strtolower(trim((string) $request->query('seccion', 'traspasos')));
        $seccion = in_array($seccion, ['traspasos', 'caducidades', 'ventas'], true) ? $seccion : 'traspasos';

        $nivel = strtolower(trim((string) $request->query('nivel', 'todos')));
        $nivel = in_array($nivel, ['todos', 'rojo', 'amarillo', 'verde'], true) ? $nivel : 'todos';

        $pendientesRecibidos = Traspaso::query()
            ->with(['sucursalOrigen', 'sucursalDestino', 'detalles.lote.producto', 'solicitadoPor'])
            ->when($selectedSucursal, fn ($query) => $query->where('sucursal_b', $selectedSucursal->id))
            ->whereIn('estado', ['pendiente', 'enviado'])
            ->orderByDesc('id')
            ->get();

        $enviados = Traspaso::query()
            ->with(['sucursalOrigen', 'sucursalDestino', 'detalles.lote.producto'])
            ->when($selectedSucursal, fn ($query) => $query->where('sucursal_a', $selectedSucursal->id))
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        $historial = Traspaso::query()
            ->with(['sucursalOrigen', 'sucursalDestino', 'detalles.lote.producto'])
            ->when($selectedSucursal, fn ($query) => $query->where(function ($sub) use ($selectedSucursal) {
                $sub->where('sucursal_b', $selectedSucursal->id)->orWhere('sucursal_a', $selectedSucursal->id);
            }))
            ->whereIn('estado', ['aceptado', 'rechazado'])
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        $caducidades = $this->caducidades($selectedSucursal?->id);
        $totalesCaducidad = [
            'rojo' => $caducidades->where('nivel', 'rojo')->count(),
            'amarillo' => $caducidades->where('nivel', 'amarillo')->count(),
            'verde' => $caducidades->where('nivel', 'verde')->count(),
            'todos' => $caducidades->count(),
        ];

        $caducidadesFiltradas = $nivel === 'todos'
            ? $caducidades
            : $caducidades->where('nivel', $nivel)->values();

        $resumenVentas = $this->resumenVentasHoy($selectedSucursal?->id);

        return view('pages.alertas.index', [
            'sucursales' => $sucursales,
            'selectedSucursal' => $selectedSucursal,
            'seccion' => $seccion,
            'nivel' => $nivel,
            'pendientesRecibidos' => $pendientesRecibidos,
            'enviados' => $enviados,
            'historial' => $historial,
            'totalPendientes' => $pendientesRecibidos->count(),
            'totalesCaducidad' => $totalesCaducidad,
            'caducidades' => $caducidadesFiltradas,
            'resumenVentas' => $resumenVentas,
        ]);
    }

    /**
     * Clasifica los lotes con existencia de la sucursal en rojo
     * (≤30 días o caducado), amarillo (31-90 o sin fecha) y verde (>90).
     *
     * Los lotes sin existencia no generan alerta: un lote agotado ya no
     * tiene riesgo de caducidad que mitigar.
     *
     * Entrada: id de sucursal opcional.
     * Salida: colección con producto, lote, sucursal, fechas, días restantes y nivel.
     */
    private function caducidades(?int $sucursalId): Collection
    {
        return Lote::query()
            ->with(['producto', 'inventarios.sucursal'])
            ->when($sucursalId, function ($query) use ($sucursalId) {
                $query->whereHas('inventarios', function ($inventarioQuery) use ($sucursalId) {
                    $inventarioQuery->where('inventario.id_sucursal', $sucursalId)
                        ->where('stock', '>', 0);
                });
            })
            ->when(! $sucursalId, function ($query) {
                $query->whereHas('inventarios', fn ($inventarioQuery) => $inventarioQuery->where('stock', '>', 0));
            })
            ->orderByRaw('COALESCE(fecha_de_caducidad, fecha_caducidad) ASC')
            ->get()
            ->map(function (Lote $lote) use ($sucursalId) {
                $fecha = ($lote->fecha_de_caducidad ?? $lote->fecha_caducidad)
                    ? Carbon::parse($lote->fecha_de_caducidad ?? $lote->fecha_caducidad)
                    : null;

                $estado = EstadoCaducidad::clasificar($fecha);
                $dias = EstadoCaducidad::diasRestantes($fecha);

                $inventarioSucursal = $sucursalId
                    ? $lote->inventarios->firstWhere('id_sucursal', $sucursalId)
                    : $lote->inventarios->first();

                return [
                    'id' => $lote->id,
                    'folio' => $lote->folio,
                    'producto' => $lote->producto?->nombre_producto ?? 'Producto sin nombre',
                    'sucursal' => $inventarioSucursal?->sucursal?->nombre_sucursal ?? 'Sin asignar',
                    'stock' => $inventarioSucursal?->stock ?? (int) $lote->stock_lote,
                    'fecha_caducidad' => $fecha?->format('Y-m-d') ?? '-',
                    'dias_restantes' => $dias,
                    'nivel' => $estado->nivelAlertas(),
                    'etiqueta' => $estado->etiquetaAlertas($dias),
                ];
            })
            ->values();
    }

    /**
     * Resume las ventas del día para la sucursal: conteo, total y ticket promedio.
     *
     * Entrada: id de sucursal opcional.
     * Salida: arreglo con total_ventas, monto_total, ticket_promedio y última venta.
     */
    private function resumenVentasHoy(?int $sucursalId): array
    {
        if (! $sucursalId) {
            return ['total_ventas' => 0, 'monto_total' => 0.0, 'ticket_promedio' => 0.0, 'ultima_venta' => null, 'por_metodo' => collect()];
        }

        $cajaIds = Caja::where('id_sucursal', $sucursalId)->pluck('id');

        $ventasHoy = Venta::query()
            ->with('pago')
            ->whereIn('id_caja', $cajaIds)
            ->whereDate('creado_en', Carbon::today())
            ->orderByDesc('creado_en')
            ->get();

        $total = (int) $ventasHoy->count();
        $monto = (float) $ventasHoy->sum('total');

        return [
            'total_ventas' => $total,
            'monto_total' => round($monto, 2),
            'ticket_promedio' => $total > 0 ? round($monto / $total, 2) : 0.0,
            'ultima_venta' => $ventasHoy->first(),
            'por_metodo' => $ventasHoy->groupBy(fn (Venta $venta) => $venta->pago?->metodo ?? 'Sin método')
                ->map(fn ($grupo) => ['ventas' => $grupo->count(), 'monto' => round((float) $grupo->sum('total'), 2)])
                ->sortDesc(),
        ];
    }
}
