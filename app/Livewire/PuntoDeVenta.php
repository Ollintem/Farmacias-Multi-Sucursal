<?php

namespace App\Livewire;

use App\Models\Caja;
use App\Models\ConfiguracionBancaria;
use App\Models\Inventario;
use App\Models\Pago;
use App\Models\PresentacionProducto;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

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

    public array $carrito = [];

    public bool $mostrandoCarrito = false;

    // Checkout modal
    public bool $abriendoCobro = false;

    public string $metodoPago = 'efectivo';

    public $efectivoRecibido = '';

    public array $pagoMixto = [
        'efectivo' => '',
        'tarjeta' => '',
        'transferencia' => '',
    ];

    public bool $procesando = false;

    public string $errorVenta = '';

    public string $referenciaTarjeta = '';

    public bool $esSuperAdmin = false;

    // Selector de presentación (productos sin precio unitario)
    public $selectorProductoId = null;

    public string $selectorNombreProducto = '';

    public array $selectorPresentaciones = [];

    // Precio unitario cuando el selector también ofrece vender por unidad.
    public ?float $selectorPrecioUnidad = null;

    // Cajas selladas y unidades sueltas del producto en la sucursal activa.
    public ?array $selectorEstadoCajas = null;

    // Receta modal (controlled meds)
    public bool $mostrandoReceta = false;

    public int $recetaProductoId = 0;

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
    }

    private function guardarCarrito(): void
    {
        session(['carrito_pos' => $this->carrito]);
    }

    public function getPresentacionesProperty(): Collection
    {
        return PresentacionProducto::orderBy('presentacion')->get();
    }

    public function getConfigBancariaProperty(): ConfiguracionBancaria
    {
        return ConfiguracionBancaria::obtener();
    }

    public function getProductosProperty(): \Illuminate\Database\Eloquent\Collection
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
            $query->where(function ($q) use ($termino) {
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

        return $productos->each(fn (Producto $producto) => $producto->setAttribute(
            'stock_sucursal',
            (int) ($stockSucursal[$producto->id] ?? 0)
        ));
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
     */
    private function unidadesRequeridas(array $item): int
    {
        $unidades = ($item['tipo_venta'] ?? 'unidad') === 'presentacion'
            ? max(1, (int) ($item['unidades'] ?? 1))
            : 1;

        return (int) $item['cantidad'] * $unidades;
    }

    /**
     * Stock disponible por producto en la sucursal activa.
     *
     * Entrada: ninguna (lee session('active_sucursal_id')).
     * Salida: arreglo [id_producto => unidades] del inventario de la sucursal;
     * vacío cuando no hay sucursal activa.
     *
     * @return array<int, int>
     */
    private function stockPorSucursal(): array
    {
        $sucursalId = (int) (session('active_sucursal_id') ?? 0);

        if ($sucursalId <= 0) {
            return [];
        }

        return Inventario::stockPorProducto($sucursalId);
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
        return round(array_reduce($this->carrito, fn ($sum, $item) => $sum + ($item['precio'] * $item['cantidad']), 0.0), 2);
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
            'mixto' => (float) $this->pagado >= $this->total,
            default => false,
        };
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
            $this->abrirSelector($productoId);

            return;
        }

        if ($producto->es_controlado) {
            $this->abrirReceta($productoId);

            return;
        }

        $this->agregarUnidadAlCarrito($productoId);
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
                }
                if ($receta !== null) {
                    $this->carrito[$index]['receta'] = $receta;
                }
                $existe = true;
                break;
            }
        }

        if (! $existe && $disponible > 0) {
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
            ->map(fn ($rel) => [
                'id' => $rel->id,
                'presentacion' => $rel->presentacion?->presentacion ?? '',
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

        if ($producto && $producto->es_controlado) {
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

        if ($maxPresentaciones < 1) {
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
                'presentacion' => $relacion->presentacion?->presentacion ?? '',
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
        if (! $producto) {
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

        $this->reset(['metodoPago', 'efectivoRecibido', 'errorVenta', 'referenciaTarjeta']);
        $this->pagoMixto = ['efectivo' => '', 'tarjeta' => '', 'transferencia' => ''];
        $this->abriendoCobro = true;
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

        // Validate payment BEFORE setting procesando (puedeConfirmar returns false when procesando=true)
        if (! $this->puedeConfirmar) {
            $this->errorVenta = 'El monto recibido es insuficiente.';

            return;
        }

        $this->procesando = true;

        try {
            // Valida stock por sucursal para todos los ítems del carrito
            $requerido = [];
            foreach ($this->carrito as $item) {
                $producto = Producto::find($item['producto_id']);

                if (! $producto) {
                    $this->errorVenta = "El producto '{$item['nombre']}' ya no existe.";
                    $this->procesando = false;

                    return;
                }

                $requerido[$item['producto_id']]['producto'] = $producto;
                $requerido[$item['producto_id']]['unidades'] = ($requerido[$item['producto_id']]['unidades'] ?? 0)
                    + $this->unidadesRequeridas($item);
            }

            foreach ($requerido as $pendiente) {
                $disponible = $this->stockDisponibleEnSucursal($pendiente['producto']->id);

                if ($disponible < $pendiente['unidades']) {
                    $this->errorVenta = "Stock insuficiente para '{$pendiente['producto']->nombre_producto}' en esta sucursal. Disponible: {$disponible}, solicitado: {$pendiente['unidades']} unidades.";
                    $this->procesando = false;

                    return;
                }
            }

            // Find or create caja for this sucursal
            $caja = Caja::firstOrCreate(['id_sucursal' => $sucursalId]);

            // Generate folio
            $folio = 'V-'.date('Ymd').'-'.str_pad(Venta::count() + 1, 5, '0', STR_PAD_LEFT);

            $resultado = DB::transaction(function () use ($caja, $folio, $sucursalId) {
                // Create payment(s)
                $pagoPrincipal = null;
                $pagosAdicionales = [];

                switch ($this->metodoPago) {
                    case 'efectivo':
                        $pagoPrincipal = Pago::create([
                            'monto' => $this->total,
                            'metodo' => 'Efectivo',
                            'estado' => 'Completado',
                            'referencia' => 'EF-'.$folio,
                        ]);
                        break;
                    case 'tarjeta':
                        $pagoPrincipal = Pago::create([
                            'monto' => $this->total,
                            'metodo' => 'Tarjeta',
                            'estado' => 'Completado',
                            'referencia' => trim($this->referenciaTarjeta) !== '' ? trim($this->referenciaTarjeta) : 'TJ-'.$folio,
                        ]);
                        break;
                    case 'transferencia':
                        $pagoPrincipal = Pago::create([
                            'monto' => $this->total,
                            'metodo' => 'Transferencia',
                            'estado' => 'Completado',
                            'referencia' => 'TR-'.$folio,
                        ]);
                        break;
                    case 'mixto':
                        if ($this->pagoMixto['efectivo'] > 0) {
                            $pagosAdicionales[] = Pago::create([
                                'monto' => $this->pagoMixto['efectivo'],
                                'metodo' => 'Efectivo',
                                'estado' => 'Completado',
                                'referencia' => 'EF-'.$folio,
                            ]);
                        }
                        if ($this->pagoMixto['tarjeta'] > 0) {
                            $pagosAdicionales[] = Pago::create([
                                'monto' => $this->pagoMixto['tarjeta'],
                                'metodo' => 'Tarjeta',
                                'estado' => 'Completado',
                                'referencia' => 'TJ-'.$folio,
                            ]);
                        }
                        if ($this->pagoMixto['transferencia'] > 0) {
                            $pagosAdicionales[] = Pago::create([
                                'monto' => $this->pagoMixto['transferencia'],
                                'metodo' => 'Transferencia',
                                'estado' => 'Completado',
                                'referencia' => 'TR-'.$folio,
                            ]);
                        }
                        $pagoPrincipal = $pagosAdicionales[0] ?? null;
                        break;
                }

                if (! $pagoPrincipal) {
                    throw new \Exception('No se pudo registrar el pago.');
                }

                // Create venta
                $venta = Venta::create([
                    'id_caja' => $caja->id,
                    'folio' => $folio,
                    'descuento' => 0,
                    'total' => $this->total,
                    'id_pago' => $pagoPrincipal->id,
                    'estado' => 'Completada',
                ]);

                // Attach products to venta via pivot
                foreach ($this->carrito as $item) {
                    $venta->productos()->attach($item['producto_id'], [
                        'cantidad' => $item['cantidad'],
                        'precio_unidad' => $item['precio'],
                    ]);

                    // Inventario de la sucursal consumiendo lotes FEFO.
                    $unidades = $this->unidadesRequeridas($item);
                    $this->consumirStockFefo((int) $sucursalId, $item['producto_id'], $unidades);
                }

                return $venta;
            });

            // Unidades que quedaron sin vender por estar en lotes caducados
            // (se calcula antes de limpiar el carrito).
            $caducadasIgnoradas = $this->unidadesCaducadasIgnoradas(
                (int) $sucursalId,
                array_values(array_unique(array_column($this->carrito, 'producto_id')))
            );

            // Clear cart
            $this->carrito = [];
            session()->forget('carrito_pos');

            $this->abriendoCobro = false;
            $this->procesando = false;

            $this->dispatch('venta-completada', folio: $resultado->folio);

            if ($caducadasIgnoradas > 0) {
                $this->dispatch('stock-caducado-ignorado', unidades: $caducadasIgnoradas);
            }

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->errorVenta = 'Error al procesar la venta: '.$e->getMessage();
            $this->procesando = false;
        }
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

    public function render()
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
        ]);
    }
}
