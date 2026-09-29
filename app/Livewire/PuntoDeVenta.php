<?php

namespace App\Livewire;

use App\Models\Caja;
use App\Models\ConfiguracionBancaria;
use App\Models\Pago;
use App\Models\PresentacionProducto;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Punto de venta')]
class PuntoDeVenta extends Component
{
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
        $query = Producto::query()
            ->with(['presentacion', 'presentacionesPrecio.presentacion'])
            ->where('es_activo', true);

        if ($this->busqueda !== '') {
            $termino = $this->busqueda;
            $query->where(function ($q) use ($termino) {
                $q->where('nombre_producto', 'like', "%{$termino}%")
                    ->orWhere('codigo_barras', 'like', "%{$termino}%");
            });
        }

        if ($this->filtroPresentacion !== '') {
            $query->where(function ($q) {
                $q->where('id_presentacion', $this->filtroPresentacion)
                    ->orWhereHas('presentacionesPrecio', function ($subQuery) {
                        $subQuery->where('id_presentacion', $this->filtroPresentacion);
                    });
            });
        }

        return $query->orderBy('nombre_producto')->get();
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

        if ($producto->precio === null) {
            $this->abrirSelector($productoId);

            return;
        }

        if ($producto->es_controlado) {
            $this->abrirReceta($productoId);

            return;
        }

        $existe = false;
        foreach ($this->carrito as $index => $item) {
            if ($item['producto_id'] === $productoId && ($item['tipo_venta'] ?? 'unidad') === 'unidad') {
                if ($this->carrito[$index]['cantidad'] < $producto->stock) {
                    $this->carrito[$index]['cantidad']++;
                }
                $existe = true;
                break;
            }
        }

        if (! $existe && $producto->stock > 0) {
            $this->carrito[] = [
                'producto_id' => $producto->id,
                'nombre' => $producto->nombre_producto,
                'precio' => (float) $producto->precio,
                'cantidad' => 1,
                'stock' => $producto->stock,
                'presentacion' => $producto->presentacion?->presentacion ?? '',
                'es_controlado' => $producto->es_controlado,
                'tipo_venta' => 'unidad',
            ];
        }

        $this->guardarCarrito();
    }

    // ─── Selector de presentación (productos sin precio unitario) ──

    public function abrirSelector(int $productoId): void
    {
        $producto = Producto::with('presentacionesPrecio.presentacion')->findOrFail($productoId);

        $this->selectorProductoId = $producto->id;
        $this->selectorNombreProducto = $producto->nombre_producto;
        $this->selectorPresentaciones = $producto->presentacionesPrecio
            ->map(fn ($rel) => [
                'id' => $rel->id,
                'presentacion' => $rel->presentacion?->presentacion ?? '',
                'unidades' => (int) $rel->unidades,
                'precio' => (float) $rel->precio_presentacion,
            ])
            ->values()
            ->all();
    }

    public function cerrarSelector(): void
    {
        $this->selectorProductoId = null;
        $this->selectorNombreProducto = '';
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

    private function agregarItemPresentacion(int $productoId, int $presentacionPrecioId, ?array $receta = null): void
    {
        $producto = Producto::with('presentacionesPrecio.presentacion')->findOrFail($productoId);
        $relacion = $producto->presentacionesPrecio->firstWhere('id', $presentacionPrecioId);

        if (! $relacion) {
            return;
        }

        $unidades = max(1, (int) $relacion->unidades);
        $maxPresentaciones = (int) floor($producto->stock / $unidades);

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
                'stock' => $producto->stock,
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

        // Add to cart with receta data
        $existe = false;
        foreach ($this->carrito as $index => $item) {
            if ($item['producto_id'] === $this->recetaProductoId && ($item['tipo_venta'] ?? 'unidad') === 'unidad') {
                if ($this->carrito[$index]['cantidad'] < $producto->stock) {
                    $this->carrito[$index]['cantidad']++;
                }
                $this->carrito[$index]['receta'] = $receta;
                $existe = true;
                break;
            }
        }

        if (! $existe && $producto->stock > 0) {
            $this->carrito[] = [
                'producto_id' => $producto->id,
                'nombre' => $producto->nombre_producto,
                'precio' => (float) $producto->precio,
                'cantidad' => 1,
                'stock' => $producto->stock,
                'presentacion' => $producto->presentacion?->presentacion ?? '',
                'es_controlado' => $producto->es_controlado,
                'tipo_venta' => 'unidad',
                'receta' => $receta,
            ];
        }

        $this->guardarCarrito();
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
            // Validate stock for all items
            foreach ($this->carrito as $item) {
                $producto = Producto::query()->withoutGlobalScope('sucursal')->find($item['producto_id']);

                if (! $producto) {
                    $this->errorVenta = "El producto '{$item['nombre']}' ya no existe.";
                    $this->procesando = false;

                    return;
                }

                if ($producto->stock < $this->unidadesRequeridas($item)) {
                    $this->errorVenta = "Stock insuficiente para '{$item['nombre']}'. Disponible: {$producto->stock}, solicitado: {$this->unidadesRequeridas($item)} unidades.";
                    $this->procesando = false;

                    return;
                }
            }

            // Find or create caja for this sucursal
            $caja = Caja::firstOrCreate(['id_sucursal' => $sucursalId]);

            // Generate folio
            $folio = 'V-'.date('Ymd').'-'.str_pad(Venta::count() + 1, 5, '0', STR_PAD_LEFT);

            $resultado = DB::transaction(function () use ($caja, $folio) {
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

                    // Decrement stock (unidades: 1 caja de 10 und descuenta 10)
                    Producto::where('id', $item['producto_id'])
                        ->decrement('stock', $this->unidadesRequeridas($item));
                }

                return $venta;
            });

            // Clear cart
            $this->carrito = [];
            session()->forget('carrito_pos');

            $this->abriendoCobro = false;
            $this->procesando = false;

            $this->dispatch('venta-completada', folio: $resultado->folio);

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->errorVenta = 'Error al procesar la venta: '.$e->getMessage();
            $this->procesando = false;
        }
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
