<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Sucursal;
use App\Models\Traspaso;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class EntradasController extends Controller
{
    /**
     * Muestra el listado unificado de entradas: compras de proveedores
     * y traspasos entre sucursales recibidos en la sucursal activa.
     *
     * Entrada: query string `sucursal`, `tipo` (todas|compras|traspasos) y `buscar`.
     * Salida: resources/views/pages/entradas/index.blade.php.
     */
    public function index(Request $request): View
    {
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        $selectedSucursalId = session('active_sucursal_id') ?? $request->query('sucursal') ?? $sucursales->first()?->id;
        $selectedSucursal = $sucursales->firstWhere('id', $selectedSucursalId) ?? $sucursales->first();

        $tipo = strtolower(trim((string) $request->query('tipo', 'todas')));
        $tipo = match ($tipo) {
            'compra', 'compras' => 'compras',
            'traspaso', 'traspasos' => 'traspasos',
            default => 'todas',
        };

        $busqueda = trim((string) $request->query('buscar', ''));

        $compras = $this->entradasCompras($selectedSucursal?->id, $busqueda);
        $traspasos = $this->entradasTraspasos($selectedSucursal?->id, $busqueda);
        $solicitudes = $this->traspasosRecibidos($selectedSucursal?->id, $busqueda);
        $solicitados = $this->traspasosSolicitados($selectedSucursal?->id, $busqueda);

        $totalCompras = $compras->count();
        $totalTraspasos = $traspasos->count();

        $entradas = match ($tipo) {
            'compras' => $compras,
            'traspasos' => $traspasos,
            default => $compras->concat($traspasos)->sortByDesc('orden_fecha')->values(),
        };

        return view('pages.entradas.index', [
            'sucursales' => $sucursales,
            'selectedSucursal' => $selectedSucursal,
            'entradas' => $entradas,
            'solicitudes' => $solicitudes,
            'solicitados' => $solicitados,
            'busqueda' => $busqueda,
            'tipo' => $tipo,
            'totalEntradas' => $entradas->count(),
            'totalCompras' => $totalCompras,
            'totalTraspasos' => $totalTraspasos,
            'unidadesRecibidas' => (int) $entradas->sum('unidades'),
        ]);
    }

    /**
     * Mapea las compras de la sucursal al formato unificado de la vista.
     *
     * Entrada: id de sucursal destino (nullable) y texto de búsqueda.
     * Salida: colección de arreglos con folio, tipo, origen, destino,
     * unidades, fecha, estado y clase de estado.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function entradasCompras(?int $sucursalId, string $busqueda): Collection
    {
        return Compra::query()
            ->with(['proveedor', 'sucursal', 'lotes'])
            ->when($sucursalId, fn ($query) => $query->where('id_sucursal', $sucursalId))
            ->when($busqueda !== '', function ($query) use ($busqueda) {
                $query->where(function ($subQuery) use ($busqueda) {
                    $subQuery->whereHas('proveedor', fn ($q) => $q->where('nombre_proveedor', 'like', "%{$busqueda}%"));
                    if (is_numeric($busqueda)) {
                        $subQuery->orWhere('id', (int) $busqueda);
                    }
                });
            })
            ->orderByDesc('id')
            ->get()
            ->map(fn (Compra $compra): array => $this->mapeaCompra($compra))
            ->values();
    }

    /**
     * Mapea una compra al formato unificado de la tabla de entradas.
     *
     * @return array<string, mixed>
     */
    private function mapeaCompra(Compra $compra): array
    {
        $fecha = $compra->entregado_en ?? $compra->created_at;
        $estado = $compra->estaRecibida() ? 'recibida' : 'pendiente';

        return [
            'id' => $compra->id,
            'folio' => "#{$compra->id}",
            'tipo' => 'compra',
            'origen' => $compra->proveedor->nombre_proveedor,
            'producto' => $compra->proveedor->nombre_proveedor,
            'destino' => $compra->sucursal->nombre_sucursal,
            'unidades' => (int) $compra->lotes->sum('stock_lote'),
            'fecha' => $fecha ? Carbon::parse($fecha)->format('Y-m-d') : '-',
            'orden_fecha' => $fecha ? (int) Carbon::parse($fecha)->timestamp : 0,
            'estado' => ucfirst($estado),
            'estado_raw' => $estado,
            'estado_class' => $this->claseEstado($estado),
            'pendiente' => $compra->estaPendiente(),
        ];
    }

    /**
     * Solicitudes recibidas: traspasos que otras sucursales piden a la
     * sucursal actual (destino). Se aceptan o rechazan desde aquí.
     *
     * Entrada: id de sucursal destino (nullable) y texto de búsqueda.
     * Salida: colección de arreglos con folio, origen, destino,
     * solicitado por, fecha, estado y si sigue pendiente.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function traspasosRecibidos(?int $sucursalId, string $busqueda): Collection
    {
        return $this->consultaTraspasos($busqueda)
            ->when($sucursalId, fn ($query) => $query->where('sucursal_b', $sucursalId))
            ->orderByDesc('id')
            ->get()
            ->map(fn (Traspaso $traspaso) => $this->mapeaTraspaso($traspaso))
            ->values();
    }

    /**
     * Traspasos solicitados: traspasos que la sucursal actual pidió a
     * otras sucursales (origen). Solo se cancelan mientras siguen pendientes.
     *
     * Entrada: id de sucursal origen (nullable) y texto de búsqueda.
     * Salida: misma forma que las solicitudes recibidas.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function traspasosSolicitados(?int $sucursalId, string $busqueda): Collection
    {
        return $this->consultaTraspasos($busqueda)
            ->when($sucursalId, fn ($query) => $query->where('sucursal_a', $sucursalId))
            ->orderByDesc('id')
            ->get()
            ->map(fn (Traspaso $traspaso) => $this->mapeaTraspaso($traspaso))
            ->values();
    }

    /**
     * Base de consulta de traspasos con relaciones y búsqueda.
     *
     * @return Builder<Traspaso>
     */
    private function consultaTraspasos(string $busqueda): Builder
    {
        return Traspaso::query()
            ->with(['sucursalOrigen', 'sucursalDestino', 'detalles', 'solicitadoPor'])
            ->when($busqueda !== '', function (Builder $query) use ($busqueda): void {
                $query->where(function (Builder $subQuery) use ($busqueda): void {
                    $subQuery->where('estado', 'like', "%{$busqueda}%")
                        ->orWhereHas('sucursalOrigen', fn (Builder $q): Builder => $q->where('nombre_sucursal', 'like', "%{$busqueda}%"))
                        ->orWhereHas('sucursalDestino', fn (Builder $q): Builder => $q->where('nombre_sucursal', 'like', "%{$busqueda}%"));
                    if (is_numeric($busqueda)) {
                        $subQuery->orWhere('id', (int) $busqueda);
                    }
                });
            });
    }

    /**
     * Mapea un traspaso al formato de las tablas de entradas.
     *
     * @return array<string, mixed>
     */
    private function mapeaTraspaso(Traspaso $traspaso): array
    {
        $origen = $traspaso->sucursalOrigen->nombre_sucursal;
        $destino = $traspaso->sucursalDestino->nombre_sucursal;

        return [
            'id' => $traspaso->id,
            'folio' => "T-{$traspaso->id}",
            'tipo' => 'traspaso',
            'origen' => $origen,
            'producto' => $origen,
            'destino' => $destino,
            'solicitado_por' => $traspaso->solicitadoPor->name,
            'unidades' => (int) $traspaso->detalles->sum('cantidad'),
            'fecha' => $traspaso->creado_en->format('Y-m-d'),
            'orden_fecha' => $traspaso->creado_en->getTimestamp(),
            'estado' => ucfirst((string) $traspaso->estado),
            'estado_class' => $this->claseEstado((string) $traspaso->estado),
            'estado_raw' => strtolower((string) $traspaso->estado),
            'pendiente' => $traspaso->esPendiente(),
        ];
    }

    /**
     * Mapea los traspasos recibidos en la sucursal al formato unificado.
     *
     * Entrada: id de sucursal destino (nullable) y texto de búsqueda.
     * Salida: colección de arreglos con el mismo formato que las compras.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function entradasTraspasos(?int $sucursalId, string $busqueda): Collection
    {
        return Traspaso::query()
            ->with(['sucursalOrigen', 'sucursalDestino', 'detalles'])
            ->when($sucursalId, function (Builder $query) use ($sucursalId): Builder {
                return $query->where(function (Builder $sub) use ($sucursalId): Builder {
                    $sub->where('sucursal_a', $sucursalId)->orWhere('sucursal_b', $sucursalId);

                    return $sub;
                });
            })
            ->when($busqueda !== '', function (Builder $query) use ($busqueda): void {
                $query->where(function (Builder $subQuery) use ($busqueda): void {
                    $subQuery->where('estado', 'like', "%{$busqueda}%")
                        ->orWhereHas('sucursalOrigen', fn (Builder $q): Builder => $q->where('nombre_sucursal', 'like', "%{$busqueda}%"))
                        ->orWhereHas('sucursalDestino', fn (Builder $q): Builder => $q->where('nombre_sucursal', 'like', "%{$busqueda}%"));
                    if (is_numeric($busqueda)) {
                        $subQuery->orWhere('id', (int) $busqueda);
                    }
                });
            })
            ->orderByDesc('id')
            ->get()
            ->map(fn (Traspaso $traspaso): array => $this->mapeaEntradaTraspaso($traspaso))
            ->values();
    }

    /**
     * Mapea un traspaso recibido al formato unificado de la tabla de entradas.
     *
     * @return array<string, mixed>
     */
    private function mapeaEntradaTraspaso(Traspaso $traspaso): array
    {
        $origen = $traspaso->sucursalOrigen->nombre_sucursal;
        $destino = $traspaso->sucursalDestino->nombre_sucursal;

        return [
            'id' => $traspaso->id,
            'folio' => "T-{$traspaso->id}",
            'tipo' => 'traspaso',
            'origen' => $origen,
            'producto' => $origen,
            'destino' => $destino,
            'unidades' => (int) $traspaso->detalles->sum('cantidad'),
            'fecha' => $traspaso->creado_en->format('Y-m-d'),
            'orden_fecha' => $traspaso->creado_en->getTimestamp(),
            'estado' => ucfirst((string) $traspaso->estado),
            'estado_class' => $this->claseEstado((string) $traspaso->estado),
        ];
    }

    /**
     * Traduce el estado del movimiento a la clase del badge de la vista.
     *
     * Entrada: estado en minúsculas tal como vive en la base de datos.
     * Salida: clase CSS usada por `.status-badge`.
     */
    private function claseEstado(string $estado): string
    {
        return match (strtolower($estado)) {
            'recibido', 'recibida', 'entregado', 'completado', 'vigente' => 'vigente',
            'cancelado', 'caducado' => 'expired',
            'enviado', 'en_transito', 'en tránsito' => 'danger',
            default => 'warning',
        };
    }
}
