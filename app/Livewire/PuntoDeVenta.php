<?php

namespace App\Livewire;

use App\Models\Alerta;
use App\Models\ConfiguracionBancaria;
use App\Models\CorteCaja;
use App\Models\Inventario;
use App\Models\Pago;
use App\Models\PresentacionProducto;
use App\Models\Producto;
use App\Models\ProductoPresentacion;
use App\Models\Sucursal;
use App\Models\TipoAlerta;
use App\Models\Venta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * @property-read EloquentCollection<int, Producto> $productos
 * @property-read EloquentCollection<int, PresentacionProducto> $presentaciones
 * @property-read ConfiguracionBancaria $configBancaria
 * @property-read int $cantidadArticulos
 * @property-read int $cantidadProductos
 * @property-read float $subTotal
 * @property-read float $total
 * @property-read float $cambio
 * @property-read float $pagado
 * @property-read bool $puedeConfirmar
 */
#[Layout('layouts.app')]
#[Title('Punto de venta')]
class PuntoDeVenta extends Component
{
    /**
     * Expresión SQL de la fecha efectiva de un lote: `fecha_de_caducidad` con
     * respaldo a `fecha_caducidad` (misma regla que Lote::estaCaducado()).
     */
    private const FECHA_EFECTIVA_LOTE = 'COALESCE(lotes.fecha_de_caducidad, lotes.fecha_caducidad)';

    public string $busqueda = '';

    public string $filtroPresentacion = '';

    /** @var array<int, array{producto_id: int, nombre: string, precio: float, cantidad: int, stock: int, presentacion: string, es_controlado: bool, tipo_venta?: string, presentacion_id?: int, unidades?: int, receta?: array<string, string>}> */
    public array $carrito = [];

    public bool $mostrandoCarrito = false;

    // Checkout modal
    public bool $abriendoCobro = false;

    public string $metodoPago = 'efectivo';

    public string $efectivoRecibido = '';

    /** @var array{efectivo: float|string, tarjeta: float|string, transferencia: float|string} */
    public array $pagoMixto = [
        'efectivo' => '',
        'tarjeta' => '',
        'transferencia' => '',
    ];

    public bool $procesando = false;

    public string $errorVenta = '';

    public string $referenciaTarjeta = '';

    // Modal para activar la caja del turno directamente desde el POS
    public bool $abriendoCaja = false;

    // Modal para cerrar (arquear) la caja propia al terminar el turno
    public bool $cerrandoCaja = false;

    public bool $esSuperAdmin = false;

    // Selector de presentación (productos sin precio unitario)
    public ?int $selectorProductoId = null;

    public string $selectorNombreProducto = '';

    /** @var array<int, array{id: int, presentacion: string, unidades: int, precio: float}> */
    public array $selectorPresentaciones = [];

    // Precio unitario cuando el selector también ofrece vender por unidad.
    public ?float $selectorPrecioUnidad = null;

    // Cajas selladas y unidades sueltas del producto en la sucursal activa.
    public ?array $selectorEstadoCajas = null;

    // Receta modal (controlled meds)
    public bool $mostrandoReceta = false;

    public int $recetaProductoId = 0;

    // Ticket térmico 80mm mostrado tras cobrar
    public bool $mostrandoTicket = false;

    /** @var array<string, mixed>|null */
    public ?array $ticketDatos = null;

    public ?int $recetaPresentacionId = null;

    public string $recetaNombreMedico = '';

    public string $recetaCedula = '';

    public string $recetaFolio = '';

    public string $recetaFecha = '';

    public string $errorReceta = '';

    public function mount(): void
    {
        $this->carrito = session('carrito_pos', []);
        $this->esSuperAdmin = auth()->user()?->rol?->tipo_rol === 'SuperAdmin';
        // Si el alta de caja falló (p. ej. caja+turno duplicados) se vuelve a
        // abrir el modal para corregir sin perder el carrito.
        $this->abriendoCaja = session()->has('error');

        // Si falló el cierre de caja, se reabre el modal de arqueo.
        $this->cerrandoCaja = $this->cortePropio !== null
            && session()->get('errors')?->has('efectivo_declarado') === true;

        // Al volver de abrir la caja desde el POS se retoma el cobro pendiente.
        if (request()->boolean('cobrar') && ! empty($this->carrito) && $this->cajaAbierta) {
            $this->abriendoCobro = true;
        }
    }

    private function guardarCarrito(): void
    {
        session(['carrito_pos' => $this->carrito]);
    }

    /**
     * @return EloquentCollection<int, PresentacionProducto>
     */
    public function getPresentacionesProperty(): EloquentCollection
    {
        return PresentacionProducto::orderBy('presentacion')->get();
    }

    public function getConfigBancariaProperty(): ConfiguracionBancaria
    {
        return ConfiguracionBancaria::obtener();
    }

    /**
     * @return EloquentCollection<int, Producto>
     */
    public function getProductosProperty(): EloquentCollection
    {
        $sucursalId = (int) (session('active_sucursal_id') ?? 0);

        $query = Producto::query()
            ->with(['presentacionesPrecio.presentacion'])
            ->where('es_activo', true)
            // Solo se ofrecen productos con inventario registrado en la
            // sucursal activa: el catálogo es global, la venta es por sucursal.
            ->when($sucursalId > 0, fn ($q) => $q->whereHas(
                'inventarios',
                fn ($inventarioQuery) => $inventarioQuery->where('inventario.id_sucursal', $sucursalId)
            ));

        if ($this->busqueda !== '') {
            $termino = $this->busqueda;
            $query->where(function (Builder $q) use ($termino): void {
                $q->where('nombre_producto', 'like', "%{$termino}%")
                    ->orWhere('codigo_barras', 'like', "%{$termino}%");
            });
        }

        if ($this->filtroPresentacion !== '') {
            $query->whereHas('presentacionesPrecio', function ($subQuery) {
                $subQuery->where('id_presentacion', $this->filtroPresentacion);
            });
        }

        $productos = $query->orderBy('nombre_producto')->get();
        $stockSucursal = $this->stockPorSucursal();
        $stockTotal = $this->stockPorSucursal(excluirCaducados: false);

        return $productos->each(function (Producto $producto) use ($stockSucursal, $stockTotal) {
            $disponible = (int) ($stockSucursal[$producto->id] ?? 0);

            $producto->setAttribute('stock_sucursal', $disponible);
            $producto->setAttribute(
                'stock_presentacion',
                $this->stockEnPresentacionFiltrada($producto, $disponible)
            );
            // Inventario con existencia pero todo en lotes caducados: no es vendible.
            $producto->setAttribute(
                'solo_caducado',
                $disponible === 0 && (int) ($stockTotal[$producto->id] ?? 0) > 0
            );
        });
    }

    /**
     * Stock disponible expresado en la presentación filtrada del header.
     *
     * Entrada: producto del catálogo y stock total en la sucursal.
     * Salida: piso de unidades ÷ unidades por presentación; null cuando no
     *         hay filtro o el producto no ofrece esa presentación.
     */
    private function stockEnPresentacionFiltrada(Producto $producto, int $disponible): ?int
    {
        if ($this->filtroPresentacion === '') {
            return null;
        }

        $relacion = $producto->presentacionesPrecio->firstWhere(
            'id_presentacion',
            (int) $this->filtroPresentacion
        );

        if ($relacion === null) {
            return null;
        }

        return (int) floor($disponible / max(1, (int) $relacion->unidades));
    }

    public function getCantidadArticulosProperty(): int
    {
        return array_sum(array_column($this->carrito, 'cantidad'));
    }

    /**
     * Unidades totales que consume un ítem del carrito en stock.
     *
     * Entrada: ítem del carrito.
     * Salida: cantidad × unidades por presentación (1 por unidad).
     *
     * @param  array{tipo_venta?: string, unidades?: int, cantidad: int}  $item
     */
    private function unidadesRequeridas(array $item): int
    {
        $unidades = ($item['tipo_venta'] ?? 'unidad') === 'presentacion'
            ? max(1, (int) ($item['unidades'] ?? 1))
            : 1;

        return (int) $item['cantidad'] * $unidades;
    }

    /**
     * Stock por producto en la sucursal activa para el catálogo POS.
     *
     * Por defecto solo cuenta lotes no caducados (misma regla que
     * Lote::estaCaducado()): el stock caducado no es vendible.
     *
     * Entrada: bandera para incluir lotes caducados (detección del badge).
     * Salida: arreglo [id_producto => unidades] del inventario de la sucursal;
     * vacío cuando no hay sucursal activa.
     *
     * @return array<int, int>
     */
    private function stockPorSucursal(bool $excluirCaducados = true): array
    {
        $sucursalId = (int) (session('active_sucursal_id') ?? 0);

        if ($sucursalId <= 0) {
            return [];
        }

        return Inventario::stockPorProducto($sucursalId, excluirCaducados: $excluirCaducados);
    }

    /**
     * Unidades disponibles de un producto en la sucursal activa.
     *
     * Solo cuenta lotes no caducados (misma regla que Lote::estaCaducado()):
     * el stock caducado no es vendible.
     *
     * Entrada: id del producto.
     * Salida: suma del inventario de la sucursal en sus lotes vigentes.
     */
    private function stockDisponibleEnSucursal(int $productoId): int
    {
        $sucursalId = (int) (session('active_sucursal_id') ?? 0);

        if ($sucursalId <= 0) {
            return 0;
        }

        $query = Inventario::forSucursal($sucursalId)
            ->join('lotes', 'lotes.id', '=', 'inventario.id_lote')
            ->where('lotes.id_producto', $productoId);

        $this->excluirLotesCaducados($query);

        return (int) $query->sum('inventario.stock_individual');
    }

    public function getCantidadProductosProperty(): int
    {
        return count($this->carrito);
    }

    public function getSubTotalProperty(): float
    {
        /** @var array<int, array{precio: float, cantidad: int}> $carrito */
        $carrito = $this->carrito;

        return round(array_reduce($carrito, fn (float $sum, array $item): float => $sum + ($item['precio'] * $item['cantidad']), 0.0), 2);
    }

    public function getTotalProperty(): float
    {
        return $this->subTotal;
    }

    public function getCambioProperty(): float
    {
        $efectivo = (float) $this->efectivoRecibido;

        if ($this->metodoPago === 'efectivo') {
            return round(max(0, $efectivo - $this->total), 2);
        }

        if ($this->metodoPago === 'mixto') {
            $efectivoMixto = (float) $this->pagoMixto['efectivo'];
            $tarjeta = (float) $this->pagoMixto['tarjeta'];
            $transferencia = (float) $this->pagoMixto['transferencia'];

            return round(max(0, $efectivoMixto - ($this->total - $tarjeta - $transferencia)), 2);
        }

        return 0.0;
    }

    public function getPagadoProperty(): float
    {
        if ($this->metodoPago === 'efectivo') {
            return (float) $this->efectivoRecibido;
        }

        if ($this->metodoPago === 'mixto') {
            return round((float) $this->pagoMixto['efectivo'] + (float) $this->pagoMixto['tarjeta'] + (float) $this->pagoMixto['transferencia'], 2);
        }

        return $this->total;
    }

    public function getPuedeConfirmarProperty(): bool
    {
        if (empty($this->carrito)) {
            return false;
        }

        if ($this->procesando) {
            return false;
        }

        return match ($this->metodoPago) {
            'efectivo' => (float) $this->efectivoRecibido >= $this->total,
            'tarjeta', 'transferencia' => true,
            'mixto' => round((float) $this->pagado, 2) === round($this->total, 2),
            default => false,
        };
    }

    /**
     * Corte de caja que recibirá la próxima venta: primero el del usuario en
     * turno; si no tiene uno, cualquier caja abierta de la sucursal activa.
     */
    public function getCorteActivoProperty(): ?CorteCaja
    {
        $sucursalId = (int) (session('active_sucursal_id') ?? 0);
        $usuarioId = (int) (auth()->id() ?? 0);

        return $this->corteActivoParaVenta($sucursalId, $usuarioId);
    }

    public function getCajaAbiertaProperty(): bool
    {
        return $this->corteActivo !== null;
    }

    /**
     * Corte abierto del usuario autenticado: es el único que puede cerrar
     * desde el POS (las demás cajas se administran en el módulo Caja).
     */
    public function getCortePropioProperty(): ?CorteCaja
    {
        $corte = $this->corteActivo;

        return $corte !== null && (int) $corte->id_usuario === (int) auth()->id()
            ? $corte
            : null;
    }

    /**
     * Turno sugerido según la hora actual: Mañana hasta las 15:00.
     */
    public function getTurnoSugeridoProperty(): string
    {
        return now()->hour < 15 ? 'Matutino' : 'Vespertino';
    }

    /**
     * Sucursal activa con la que opera el punto de venta.
     */
    public function getSucursalActivaProperty(): ?Sucursal
    {
        return Sucursal::find((int) (session('active_sucursal_id') ?? 0));
    }

    public function agregarAlCarrito(int $productoId): void
    {
        $producto = Producto::with('presentacionesPrecio.presentacion')->findOrFail($productoId);

        // Un producto unitario (su única presentación es «unidad») se agrega
        // directo. Con cualquier otra presentación el selector decide la vía.
        $otrasPresentaciones = $producto->presentacionesPrecio->filter(
            fn ($presentacion): bool => strcasecmp($presentacion->presentacion?->presentacion ?? '', 'Unidad') !== 0
        );

        if ($otrasPresentaciones->isNotEmpty()) {
            // Con una presentación filtrada en el header no se pregunta: se
            // agrega directo en esa presentación. El selector es solo de «Todos».
            if ($this->agregarEnPresentacionFiltrada($producto)) {
                return;
            }

            $this->abrirSelector($productoId);

            return;
        }

        if ($producto->es_controlado) {
            $this->abrirReceta($productoId);

            return;
        }

        $this->agregarUnidadAlCarrito($productoId);
    }

    /**
     * Agrega el producto en la presentación elegida en el filtro del header.
     *
     * @return bool true si el producto tenía esa presentación y ya se agregó
     *              (o se pidió la receta); false para recurrir al selector.
     */
    private function agregarEnPresentacionFiltrada(Producto $producto): bool
    {
        if ($this->filtroPresentacion === '') {
            return false;
        }

        $relacion = $producto->presentacionesPrecio->firstWhere(
            'id_presentacion',
            (int) $this->filtroPresentacion
        );

        if ($relacion === null) {
            return false;
        }

        $this->cerrarSelector();

        if (strcasecmp($relacion->presentacion?->presentacion ?? '', 'Unidad') === 0) {
            if ($producto->es_controlado) {
                $this->recetaPresentacionId = null;
                $this->abrirReceta($producto->id);

                return true;
            }

            $this->agregarUnidadAlCarrito($producto->id);

            return true;
        }

        if ($producto->es_controlado) {
            $this->recetaPresentacionId = $relacion->id;
            $this->abrirReceta($producto->id);

            return true;
        }

        $this->agregarItemPresentacion($producto->id, $relacion->id);

        return true;
    }

    private function agregarUnidadAlCarrito(int $productoId, ?array $receta = null): void
    {
        $producto = Producto::with('presentacionesPrecio.presentacion')->findOrFail($productoId);
        $precioUnidad = $producto->precioUnidad();

        if ($precioUnidad === null) {
            return;
        }

        $disponible = $this->stockDisponibleEnSucursal($productoId);

        $existe = false;
        foreach ($this->carrito as $index => $item) {
            if ($item['producto_id'] === $productoId && ($item['tipo_venta'] ?? 'unidad') === 'unidad') {
                if ($this->carrito[$index]['cantidad'] < $disponible) {
                    $this->carrito[$index]['cantidad']++;
                } elseif ($disponible > 0) {
                    $this->avisarCantidadMaxima($producto->nombre_producto, $disponible);
                } else {
                    $this->avisarSinStock($producto);
                }
                if ($receta !== null) {
                    $this->carrito[$index]['receta'] = $receta;
                }
                $existe = true;
                break;
            }
        }

        if (! $existe) {
            if ($disponible > 0) {
                $item = [
                    'producto_id' => $producto->id,
                    'nombre' => $producto->nombre_producto,
                    'precio' => $precioUnidad,
                    'cantidad' => 1,
                    'stock' => $disponible,
                    'presentacion' => '',
                    'es_controlado' => $producto->es_controlado,
                    'tipo_venta' => 'unidad',
                ];

                if ($receta !== null) {
                    $item['receta'] = $receta;
                }

                $this->carrito[] = $item;
            } else {
                $this->avisarSinStock($producto);
            }
        }

        $this->guardarCarrito();
    }

    // ─── Selector de venta (presentaciones y/o unidad) ──

    public function abrirSelector(int $productoId): void
    {
        $producto = Producto::with('presentacionesPrecio.presentacion')->findOrFail($productoId);

        $disponible = $this->stockDisponibleEnSucursal($productoId);

        $this->selectorProductoId = $producto->id;
        $this->selectorNombreProducto = $producto->nombre_producto;
        $this->selectorPrecioUnidad = $producto->precioUnidad();
        $this->selectorEstadoCajas = $producto->estadoDeCajas($disponible);
        $this->selectorPresentaciones = $producto->presentacionesPrecio
            ->map(fn (ProductoPresentacion $rel): array => [
                'id' => $rel->id,
                'presentacion' => $rel->presentacion->presentacion,
                'unidades' => (int) $rel->unidades,
                'precio' => (float) $rel->precio_presentacion,
                'maximo' => (int) floor($disponible / max(1, (int) $rel->unidades)),
            ])
            ->values()
            ->all();
    }

    public function cerrarSelector(): void
    {
        $this->selectorProductoId = null;
        $this->selectorNombreProducto = '';
        $this->selectorPrecioUnidad = null;
        $this->selectorEstadoCajas = null;
        $this->selectorPresentaciones = [];
    }

    public function elegirPresentacion(int $presentacionPrecioId): void
    {
        $productoId = $this->selectorProductoId;

        if (! $productoId) {
            return;
        }

        $producto = Producto::find($productoId);

        if ($producto instanceof Producto && $producto->es_controlado) {
            $this->recetaPresentacionId = $presentacionPrecioId;
            $this->cerrarSelector();
            $this->abrirReceta($productoId);

            return;
        }

        $this->agregarItemPresentacion($productoId, $presentacionPrecioId);
        $this->cerrarSelector();
    }

    public function elegirUnidad(): void
    {
        $productoId = $this->selectorProductoId;

        if (! $productoId) {
            return;
        }

        $producto = Producto::find($productoId);

        if ($producto === null || $producto->precioUnidad() === null) {
            $this->cerrarSelector();

            return;
        }

        if ($producto->es_controlado) {
            $this->recetaPresentacionId = null;
            $this->cerrarSelector();
            $this->abrirReceta($productoId);

            return;
        }

        $this->agregarUnidadAlCarrito($productoId);
        $this->cerrarSelector();
    }

    /**
     * @param  array<string, string>|null  $receta
     */
    private function agregarItemPresentacion(int $productoId, int $presentacionPrecioId, ?array $receta = null): void
    {
        $producto = Producto::with('presentacionesPrecio.presentacion')->findOrFail($productoId);
        $relacion = $producto->presentacionesPrecio->firstWhere('id', $presentacionPrecioId);

        if (! $relacion) {
            return;
        }

        $unidades = max(1, (int) $relacion->unidades);
        $disponible = $this->stockDisponibleEnSucursal($productoId);
        $maxPresentaciones = (int) floor($disponible / $unidades);
        $etiqueta = $producto->nombre_producto.' ('.($relacion->presentacion?->presentacion ?? '').')';

        if ($maxPresentaciones < 1) {
            if ($disponible === 0) {
                $this->avisarSinStock($producto);
            } else {
                $this->dispatch('pos-error', mensaje: "No se agregó «{$etiqueta}»: el stock disponible ({$disponible} uds.) no alcanza para una presentación completa.");
            }

            return;
        }

        $existe = false;
        foreach ($this->carrito as $index => $item) {
            if (
                $item['producto_id'] === $productoId
                && ($item['tipo_venta'] ?? 'unidad') === 'presentacion'
                && (int) ($item['presentacion_id'] ?? 0) === $presentacionPrecioId
            ) {
                if ($this->carrito[$index]['cantidad'] < $maxPresentaciones) {
                    $this->carrito[$index]['cantidad']++;
                } else {
                    $this->avisarCantidadMaxima($etiqueta, $maxPresentaciones);
                }
                if ($receta !== null) {
                    $this->carrito[$index]['receta'] = $receta;
                }
                $existe = true;
                break;
            }
        }

        if (! $existe) {
            $item = [
                'producto_id' => $producto->id,
                'nombre' => $producto->nombre_producto,
                'precio' => (float) $relacion->precio_presentacion,
                'cantidad' => 1,
                'stock' => $disponible,
                'presentacion' => $relacion->presentacion->presentacion,
                'es_controlado' => $producto->es_controlado,
                'tipo_venta' => 'presentacion',
                'presentacion_id' => $relacion->id,
                'unidades' => $unidades,
            ];

            if ($receta !== null) {
                $item['receta'] = $receta;
            }

            $this->carrito[] = $item;
        }

        $this->guardarCarrito();
    }

    public function eliminarDelCarrito(int $index): void
    {
        if (isset($this->carrito[$index])) {
            array_splice($this->carrito, $index, 1);
            $this->guardarCarrito();
        }
    }

    public function actualizarCantidad(int $index, int $cantidad): void
    {
        if (! isset($this->carrito[$index])) {
            return;
        }

        $cantidad = max(1, $cantidad);
        $item = $this->carrito[$index];
        $unidades = ($item['tipo_venta'] ?? 'unidad') === 'presentacion'
            ? max(1, (int) ($item['unidades'] ?? 1))
            : 1;
        $maximo = (int) floor($item['stock'] / $unidades);
        $cantidad = min($cantidad, max(1, $maximo));

        $this->carrito[$index]['cantidad'] = $cantidad;
        $this->guardarCarrito();
    }

    public function limpiarCarrito(): void
    {
        $this->carrito = [];
        $this->guardarCarrito();
    }

    // ─── Receta (medicamentos controlados) ─────────────────────

    public function abrirReceta(int $productoId): void
    {
        $producto = Producto::findOrFail($productoId);

        if (! $producto->es_controlado) {
            $this->agregarAlCarrito($productoId);

            return;
        }

        $this->recetaProductoId = $productoId;
        $this->recetaNombreMedico = '';
        $this->recetaCedula = '';
        $this->recetaFolio = '';
        $this->recetaFecha = '';
        $this->errorReceta = '';
        $this->mostrandoReceta = true;
    }

    public function cerrarReceta(): void
    {
        $this->mostrandoReceta = false;
        $this->errorReceta = '';
        $this->recetaPresentacionId = null;
    }

    public function cerrarTicket(): void
    {
        $this->mostrandoTicket = false;
        $this->ticketDatos = null;
        // Al cerrar sin imprimir no se registra la venta: el carrito se mantiene.
    }

    /**
     * Registra la venta y dispara la impresión.
     * Solo al imprimir se crea Venta/Pago y se descuenta stock.
     */
    public function imprimirTicket(): void
    {
        if (empty($this->carrito) || $this->ticketDatos === null) {
            return;
        }

        if ($this->procesando) {
            return;
        }

        $sucursalId = session('active_sucursal_id');
        $usuario = auth()->user();

        if (! $sucursalId || ! $usuario) {
            $this->errorVenta = 'Sesion invalida. Inicia sesion nuevamente.';

            return;
        }

        $corteActivo = $this->corteActivoParaVenta((int) $sucursalId, (int) $usuario->id);
        if (! $corteActivo) {
            $this->errorVenta = 'No hay caja abierta en esta sucursal. Abre una caja en el módulo Caja antes de cobrar.';

            return;
        }

        if (! $this->puedeConfirmar) {
            $this->errorVenta = 'El monto recibido es insuficiente.';

            return;
        }

        $this->procesando = true;

        try {
            /** @var array<int, array{producto: Producto, unidades: int}> $requerido */
            $requerido = [];
            foreach ($this->carrito as $item) {
                $producto = Producto::find($item['producto_id']);
                if (! $producto instanceof Producto) {
                    $this->errorVenta = "El producto '{$item['nombre']}' ya no existe.";
                    $this->procesando = false;

                    return;
                }
                $requerido[$item['producto_id']]['producto'] = $producto;
                $requerido[$item['producto_id']]['unidades'] = ($requerido[$item['producto_id']]['unidades'] ?? 0) + $this->unidadesRequeridas($item);
            }
            foreach ($requerido as $pendiente) {
                $disponible = $this->stockDisponibleEnSucursal($pendiente['producto']->id);
                if ($disponible < $pendiente['unidades']) {
                    $this->errorVenta = "Stock insuficiente para '{$pendiente['producto']->nombre_producto}' en esta sucursal. Disponible: {$disponible}, solicitado: {$pendiente['unidades']} unidades.";
                    $this->procesando = false;

                    return;
                }
            }

            $folio = 'V-'.date('Ymd').'-'.str_pad((string) (Venta::count() + 1), 5, '0', STR_PAD_LEFT);

            $resultado = DB::transaction(function () use ($folio, $sucursalId, $corteActivo) {
                $pagoPrincipal = null;
                $pagosAdicionales = [];
                switch ($this->metodoPago) {
                    case 'efectivo':
                        $pagoPrincipal = Pago::create(['monto' => $this->total, 'metodo' => 'Efectivo', 'estado' => 'Completado', 'referencia' => 'EF-'.$folio]);
                        break;
                    case 'tarjeta':
                        $pagoPrincipal = Pago::create(['monto' => $this->total, 'metodo' => 'Tarjeta', 'estado' => 'Completado', 'referencia' => trim($this->referenciaTarjeta) !== '' ? trim($this->referenciaTarjeta) : 'TJ-'.$folio]);
                        break;
                    case 'transferencia':
                        $pagoPrincipal = Pago::create(['monto' => $this->total, 'metodo' => 'Transferencia', 'estado' => 'Completado', 'referencia' => 'TR-'.$folio]);
                        break;
                    case 'mixto':
                        if ($this->pagoMixto['efectivo'] > 0) {
                            $pagosAdicionales[] = Pago::create(['monto' => $this->pagoMixto['efectivo'], 'metodo' => 'Efectivo', 'estado' => 'Completado', 'referencia' => 'EF-'.$folio]);
                        }
                        if ($this->pagoMixto['tarjeta'] > 0) {
                            $pagosAdicionales[] = Pago::create(['monto' => $this->pagoMixto['tarjeta'], 'metodo' => 'Tarjeta', 'estado' => 'Completado', 'referencia' => 'TJ-'.$folio]);
                        }
                        if ($this->pagoMixto['transferencia'] > 0) {
                            $pagosAdicionales[] = Pago::create(['monto' => $this->pagoMixto['transferencia'], 'metodo' => 'Transferencia', 'estado' => 'Completado', 'referencia' => 'TR-'.$folio]);
                        }
                        $pagoPrincipal = $pagosAdicionales[0] ?? null;
                        break;
                }
                if (! $pagoPrincipal) {
                    throw new \Exception('No se pudo registrar el pago.');
                }
                $venta = Venta::create([
                    'id_sucursal' => (int) $sucursalId,
                    'id_corte_caja' => $corteActivo?->id,
                    'folio' => $folio,
                    'descuento' => 0,
                    'total' => $this->total,
                    'id_pago' => $pagoPrincipal->id,
                    'estado' => 'Completada',
                ]);
                foreach ($this->carrito as $item) {
                    $venta->productos()->attach($item['producto_id'], ['cantidad' => $item['cantidad'], 'precio_unidad' => $item['precio']]);
                    $unidades = $this->unidadesRequeridas($item);
                    $this->consumirStockFefo((int) $sucursalId, $item['producto_id'], $unidades);
                }

                return $venta;
            });

            $caducadasIgnoradas = $this->unidadesCaducadasIgnoradas((int) $sucursalId, array_values(array_unique(array_column($this->carrito, 'producto_id'))));

            try {
                $tipoVentas = TipoAlerta::where('nombre', 'VENTAS_RESUMEN')->first();
                if ($tipoVentas) {
                    Alerta::create(['id_tipo_alerta' => $tipoVentas->id, 'id_sucursal' => (int) $sucursalId, 'entidad_tipo' => 'venta', 'entidad_id' => $resultado->id, 'estado' => Alerta::ESTADO_ACTIVA]);
                }
            } catch (\Throwable $e) {
            }

            // Actualizar ticket con folio/fecha definitivos
            $this->ticketDatos['folio'] = $resultado->folio;
            $this->ticketDatos['fecha'] = ($resultado->creado_en ?? now())->format('d/m/Y H:i');

            $carritoTicket = $this->carrito;
            $this->carrito = [];
            session()->forget('carrito_pos');
            $this->abriendoCobro = false;
            $this->dispatch('venta-completada', folio: $resultado->folio);
            if ($caducadasIgnoradas > 0) {
                $this->dispatch('stock-caducado-ignorado', unidades: $caducadasIgnoradas);
            }
            $this->dispatch('imprimir-ticket');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->errorVenta = 'Error al procesar la venta: '.$e->getMessage();
        } finally {
            $this->procesando = false;
        }
    }

    public function validarYAgregar(): void
    {
        $this->errorReceta = '';

        $nombreMedico = trim($this->recetaNombreMedico);
        $cedula = trim($this->recetaCedula);
        $folio = trim($this->recetaFolio);
        $fecha = trim($this->recetaFecha);

        if ($nombreMedico === '' || $cedula === '' || $folio === '' || $fecha === '') {
            $this->errorReceta = 'Todos los campos de la receta son obligatorios.';

            return;
        }

        $producto = Producto::find($this->recetaProductoId);
        if (! $producto instanceof Producto) {
            $this->errorReceta = 'El producto ya no esta disponible.';
            $this->mostrandoReceta = false;
            $this->recetaPresentacionId = null;

            return;
        }

        $receta = [
            'nombre_medico' => $nombreMedico,
            'cedula_profesional' => $cedula,
            'folio_receta' => $folio,
            'fecha_receta' => $fecha,
        ];

        // Producto controlado elegido desde el selector de presentación
        if ($this->recetaPresentacionId !== null) {
            $presentacionId = $this->recetaPresentacionId;
            $this->recetaPresentacionId = null;
            $this->mostrandoReceta = false;
            $this->agregarItemPresentacion($producto->id, $presentacionId, $receta);

            return;
        }

        // Venta unitaria de un producto controlado, ya validada la receta
        $this->agregarUnidadAlCarrito($producto->id, $receta);
        $this->mostrandoReceta = false;
    }

    // ─── Checkout ──────────────────────────────────────────────

    public function abrirCobro(): void
    {
        if (empty($this->carrito)) {
            return;
        }

        // Sin caja abierta no se puede cobrar: se abre el modal para activarla.
        if (! $this->cajaAbierta) {
            $this->abrirModalCaja();

            return;
        }

        $this->procesando = false;
        $this->reset(['metodoPago', 'efectivoRecibido', 'errorVenta', 'referenciaTarjeta']);
        $this->pagoMixto = ['efectivo' => '', 'tarjeta' => '', 'transferencia' => ''];
        $this->abriendoCobro = true;
    }

    public function abrirModalCaja(): void
    {
        $this->abriendoCobro = false;
        $this->errorVenta = '';
        $this->abriendoCaja = true;
    }

    public function cerrarModalCaja(): void
    {
        $this->abriendoCaja = false;
    }

    public function abrirModalCierre(): void
    {
        if ($this->cortePropio === null) {
            return;
        }

        $this->abriendoCaja = false;
        $this->abriendoCobro = false;
        $this->errorVenta = '';
        $this->cerrandoCaja = true;
    }

    public function cerrarModalCierre(): void
    {
        $this->cerrandoCaja = false;
    }

    /**
     * Abre el cierre de caja cuando el topbar o el aviso de turno lo piden.
     */
    #[On('pos-cerrar-caja')]
    public function abrirCierreDesdeAviso(): void
    {
        $this->abrirModalCierre();
    }

    public function cerrarCobro(): void
    {
        $this->abriendoCobro = false;
        $this->errorVenta = '';
        $this->procesando = false;
    }

    public function seleccionarMetodo(string $metodo): void
    {
        $this->metodoPago = $metodo;
        $this->errorVenta = '';

        if ($metodo === 'mixto') {
            $this->pagoMixto = ['efectivo' => '', 'tarjeta' => '', 'transferencia' => ''];
        }
    }

    public function setEfectivoRapido(float $monto): void
    {
        $this->efectivoRecibido = round((float) $this->efectivoRecibido + $monto, 2);
    }

    public function limpiarEfectivo(): void
    {
        $this->efectivoRecibido = '';
    }

    public function updatedEfectivoRecibido(): void
    {
        // Keep empty string as-is for placeholder; casting happens in computed properties
    }

    public function confirmarVenta(): void
    {
        if ($this->procesando || empty($this->carrito)) {
            return;
        }

        $this->errorVenta = '';

        $sucursalId = session('active_sucursal_id');
        $usuario = auth()->user();

        if (! $sucursalId || ! $usuario) {
            $this->errorVenta = 'Sesion invalida. Inicia sesion nuevamente.';

            return;
        }

        // Validar caja abierta para la sucursal (multi-caja: cualquier caja abierta de la sucursal, prioriza la del usuario)
        $corteActivo = $this->corteActivoParaVenta((int) $sucursalId, (int) $usuario->id);
        if (! $corteActivo) {
            $this->errorVenta = 'No hay caja abierta en esta sucursal. Abre una caja en el módulo Caja antes de cobrar.';

            return;
        }

        // Validate payment BEFORE setting procesando (puedeConfirmar returns false when procesando=true)
        if (! $this->puedeConfirmar) {
            if ($this->metodoPago === 'mixto' && round((float) $this->pagado, 2) > round($this->total, 2)) {
                $this->errorVenta = 'El monto excede el total. Ajusta los montos para que la suma sea exacta.';
            } else {
                $this->errorVenta = 'El monto recibido es insuficiente.';
            }

            return;
        }

        // Validar stock de forma anticipada para el preview
        /** @var array<int, array{producto: Producto, unidades: int}> $requerido */
        $requerido = [];
        foreach ($this->carrito as $item) {
            $producto = Producto::find($item['producto_id']);
            if (! $producto instanceof Producto) {
                $this->errorVenta = "El producto '{$item['nombre']}' ya no existe.";

                return;
            }
            $requerido[$item['producto_id']]['producto'] = $producto;
            $requerido[$item['producto_id']]['unidades'] = ($requerido[$item['producto_id']]['unidades'] ?? 0) + $this->unidadesRequeridas($item);
        }
        foreach ($requerido as $pendiente) {
            $disponible = $this->stockDisponibleEnSucursal($pendiente['producto']->id);
            if ($disponible < $pendiente['unidades']) {
                $this->errorVenta = "Stock insuficiente para '{$pendiente['producto']->nombre_producto}' en esta sucursal. Disponible: {$disponible}, solicitado: {$pendiente['unidades']} unidades.";

                return;
            }
        }

        // Solo preview: la venta aún NO se registra. Se registrará al imprimir.
        $sucursalTicket = $sucursalId ? Sucursal::find((int) $sucursalId) : null;
        $folioPreview = 'V-'.date('Ymd').'-'.str_pad((string) (Venta::count() + 1), 5, '0', STR_PAD_LEFT);
        $this->ticketDatos = [
            'folio' => $folioPreview,
            'fecha' => now()->format('d/m/Y H:i'),
            'sucursal' => $sucursalTicket?->nombre_sucursal ?? '—',
            'sucursalDireccion' => $sucursalTicket?->direccion ?? null,
            'caja' => $corteActivo?->caja ?? '—',
            'turno' => $corteActivo?->turno ?? '—',
            'cajero' => $usuario->name ?? trim(($usuario->nombre ?? '').' '.($usuario->apellido ?? '')),
            'items' => array_map(fn (array $it): array => [
                'nombre' => $it['nombre'],
                'presentacion' => $it['presentacion'] ?? '',
                'tipo_venta' => $it['tipo_venta'] ?? 'unidad',
                'unidades' => $it['unidades'] ?? 1,
                'cantidad' => (int) $it['cantidad'],
                'precio' => (float) $it['precio'],
                'importe' => (float) $it['precio'] * (int) $it['cantidad'],
            ], $this->carrito),
            'subtotal' => $this->subTotal,
            'total' => $this->total,
            'metodoPago' => $this->metodoPago,
            'pagado' => $this->pagado,
            'cambio' => $this->cambio,
            'efectivoRecibido' => $this->efectivoRecibido,
            'pagoMixto' => $this->pagoMixto,
            'referenciaTarjeta' => $this->referenciaTarjeta,
        ];
        $this->mostrandoTicket = true;
        $this->abriendoCobro = false;
    }

    /**
     * Consume inventario de la sucursal por FEFO entre lotes no caducados:
     * primero el lote vigente más cercano a caducar. Lanza RuntimeException
     * cuando el stock vigente no alcanza.
     *
     * Entrada: id de la sucursal, id del producto y unidades a consumir.
     * Salida: void.
     *
     * @throws \RuntimeException Cuando el inventario de la sucursal no alcanza.
     */
    private function consumirStockFefo(int $sucursalId, int $productoId, int $unidades): void
    {
        $query = Inventario::query()
            ->join('lotes', 'lotes.id', '=', 'inventario.id_lote')
            ->where('inventario.id_sucursal', $sucursalId)
            ->where('lotes.id_producto', $productoId)
            ->where('inventario.stock_individual', '>', 0);

        $this->excluirLotesCaducados($query);

        $filas = $query
            ->orderByRaw(self::FECHA_EFECTIVA_LOTE.' ASC')
            ->select('inventario.*')
            ->lockForUpdate()
            ->get();

        $disponible = (int) $filas->sum('stock_individual');

        if ($disponible < $unidades) {
            throw new \RuntimeException("Stock insuficiente en la sucursal. Disponible: {$disponible} uds, solicitado: {$unidades} uds.");
        }

        $restante = $unidades;

        foreach ($filas as $fila) {
            if ($restante <= 0) {
                break;
            }

            $tomar = min((int) $fila->stock_individual, $restante);
            $fila->ajustarUnidadesIndividuales(-$tomar);
            $restante -= $tomar;
        }
    }

    /**
     * Excluye los lotes caducados con la regla de Lote::estaCaducado(): la
     * fecha efectiva pasada cuenta como caducada —el propio día de caducidad
     * ya se considera caducado— y un lote sin fecha no se excluye.
     *
     * Entrada: consulta de inventario unida a lotes.
     * Salida: void (la consulta se filtra en el lugar).
     */
    private function excluirLotesCaducados(Builder $query): void
    {
        $hoy = now()->toDateString();

        $query->where(function (Builder $sub) use ($hoy) {
            $sub->whereNull('lotes.fecha_de_caducidad')
                ->whereNull('lotes.fecha_caducidad')
                ->orWhereRaw(self::FECHA_EFECTIVA_LOTE.' > ?', [$hoy]);
        });
    }

    /**
     * Unidades de la sucursal que quedaron sin vender por estar en lotes
     * caducados, para los productos de la venta recién confirmada.
     *
     * Entrada: id de la sucursal y lista de ids de producto vendidos.
     * Salida: suma de inventario con stock > 0 en lotes ya caducados.
     */
    private function unidadesCaducadasIgnoradas(int $sucursalId, array $productoIds): int
    {
        if ($sucursalId <= 0 || $productoIds === []) {
            return 0;
        }

        return (int) Inventario::forSucursal($sucursalId)
            ->join('lotes', 'lotes.id', '=', 'inventario.id_lote')
            ->whereIn('lotes.id_producto', $productoIds)
            ->where('inventario.stock_individual', '>', 0)
            ->whereRaw(self::FECHA_EFECTIVA_LOTE.' IS NOT NULL')
            ->whereRaw(self::FECHA_EFECTIVA_LOTE.' <= ?', [now()->toDateString()])
            ->sum('inventario.stock_individual');
    }

    /**
     * Avisa por toast que un producto no se pudo agregar por falta de stock.
     *
     * Distingue el caso "solo lotes caducados" del "sin existencias" simple.
     *
     * Entrada: producto intentado.
     * Salida: void (despacha el evento pos-error al navegador).
     */
    private function avisarSinStock(Producto $producto): void
    {
        $mensaje = $this->hayLotesCaducadosConStock($producto->id)
            ? "No se agregó «{$producto->nombre_producto}»: solo tiene lotes caducados."
            : "No se agregó «{$producto->nombre_producto}»: sin existencias disponibles.";

        $this->dispatch('pos-error', mensaje: $mensaje);
    }

    /**
     * Avisa por toast que se alcanzó la cantidad máxima disponible en el carrito.
     *
     * Entrada: etiqueta del producto/ítem y máximo alcanzable.
     * Salida: void (despacha el evento pos-error al navegador).
     */
    private function avisarCantidadMaxima(string $etiqueta, int $disponible): void
    {
        $this->dispatch('pos-error', mensaje: "Cantidad máxima de «{$etiqueta}» alcanzada: {$disponible} disponible(s).");
    }

    /**
     * Indica si el producto aún tiene inventario en la sucursal pero
     * todo su stock está en lotes ya caducados.
     *
     * Entrada: id del producto.
     * Salida: true cuando existe inventario y todo caducado.
     */
    private function hayLotesCaducadosConStock(int $productoId): bool
    {
        $sucursalId = (int) (session('active_sucursal_id') ?? 0);

        if ($sucursalId <= 0) {
            return false;
        }

        return Inventario::forSucursal($sucursalId)
            ->join('lotes', 'lotes.id', '=', 'inventario.id_lote')
            ->where('lotes.id_producto', $productoId)
            ->where('inventario.stock_individual', '>', 0)
            ->whereRaw(self::FECHA_EFECTIVA_LOTE.' IS NOT NULL')
            ->whereRaw(self::FECHA_EFECTIVA_LOTE.' <= ?', [now()->toDateString()])
            ->exists();
    }

    /**
     * Corte activo para asignar la venta: solo la caja abierta del propio usuario.
     * No se permite usar la caja de un compañero.
     */
    private function corteActivoParaVenta(int $sucursalId, int $usuarioId): ?CorteCaja
    {
        if ($sucursalId <= 0 || $usuarioId <= 0) {
            return null;
        }

        return CorteCaja::where('id_sucursal', $sucursalId)
            ->where('estado', 'abierto')
            ->where('id_usuario', $usuarioId)
            ->orderByDesc('fecha_inicio')
            ->first();
    }

    public function render(): View
    {
        return view('livewire.punto-de-venta', [
            'productos' => $this->productos,
            'presentaciones' => $this->presentaciones,
            'cantidadArticulos' => $this->cantidadArticulos,
            'cantidadProductos' => $this->cantidadProductos,
            'subTotal' => $this->subTotal,
            'total' => $this->total,
            'cambio' => $this->cambio,
            'pagado' => $this->pagado,
            'puedeConfirmar' => $this->puedeConfirmar,
            'configBancaria' => $this->configBancaria,
            'esSuperAdmin' => $this->esSuperAdmin,
            'corteActivo' => $this->corteActivo,
            'cortePropio' => $this->cortePropio,
            'cajaAbierta' => $this->cajaAbierta,
            'turnoSugerido' => $this->turnoSugerido,
            'sucursalActiva' => $this->sucursalActiva,
        ]);
    }
}
