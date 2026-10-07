<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Models\Lote;
use App\Models\Merma;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Sucursal;
use App\Models\Traspaso;
use App\Support\EstadoCaducidad;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LotesController extends Controller
{
    /**
     * Muestra lotes y su producto asociado, filtrados por sucursal vía inventario.
     *
     * El vínculo con la sucursal vive en `inventario` (`id_sucursal`, `id_lote`).
     * Los lotes sin filas de inventario (sin asignar) se muestran en todas las
     * sucursales para no ocultar registros recién capturados.
     *
     * Entrada: query string `sucursal`, `buscar` y `estado` (filtro de tarjetas).
     * Salida: resources/views/pages/lotes/index.blade.php con estados de caducidad.
     */
    public function index(Request $request): View
    {
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        $selectedSucursalId = session('active_sucursal_id') ?? $request->query('sucursal') ?? $sucursales->first()?->id;
        $selectedSucursal = $sucursales->firstWhere('id', $selectedSucursalId) ?? $sucursales->first();
        $busqueda = trim((string) $request->query('buscar', ''));
        $filtroEstado = $request->query('estado', 'todos');

        if (! is_string($filtroEstado) || ! in_array($filtroEstado, ['todos', 'vigentes', 'por-caducar', 'caducados'], true)) {
            $filtroEstado = 'todos';
        }

        $lotes = Lote::with(['producto', 'pedido.proveedor', 'proveedor', 'inventarios.sucursal'])
            ->when($selectedSucursal, function ($query, $sucursal) {
                $query->where(function ($subQuery) use ($sucursal) {
                    $subQuery->whereHas('inventarios', function ($inventarioQuery) use ($sucursal) {
                        $inventarioQuery->where('inventario.id_sucursal', $sucursal->id);
                    })->orWhereDoesntHave('inventarios');
                });
            })
            ->when($busqueda !== '', function ($query) use ($busqueda) {
                $query->where(function ($subQuery) use ($busqueda) {
                    $subQuery->where('folio', 'like', "%{$busqueda}%")
                        ->orWhereHas('proveedor', function ($proveedorQuery) use ($busqueda) {
                            $proveedorQuery->where('nombre_proveedor', 'like', "%{$busqueda}%");
                        })
                        ->orWhereHas('pedido.proveedor', function ($proveedorQuery) use ($busqueda) {
                            $proveedorQuery->where('nombre_proveedor', 'like', "%{$busqueda}%");
                        })
                        ->orWhereHas('producto', function ($productoQuery) use ($busqueda) {
                            $productoQuery->where('nombre_producto', 'like', "%{$busqueda}%");
                        });
                });
            })
            ->orderBy('fecha_caducidad')
            ->get()
            ->map(function ($lote) use ($selectedSucursal) {
                $producto = $lote->producto;
                $inventarioSucursal = $selectedSucursal
                    ? $lote->inventarios->firstWhere('id_sucursal', $selectedSucursal->id)
                    : $lote->inventarios->first();
                $nombreProducto = $producto?->nombre_producto ?? 'Producto sin nombre';
                $nombreSucursal = $inventarioSucursal?->sucursal?->nombre_sucursal ?? $selectedSucursal?->nombre_sucursal ?? 'Sin sucursal';
                $fechaCaducidad = ($lote->fecha_de_caducidad ?? $lote->fecha_caducidad)
                    ? Carbon::parse($lote->fecha_de_caducidad ?? $lote->fecha_caducidad)
                    : null;

                $clase = EstadoCaducidad::clasificar($fechaCaducidad);
                $estado = $clase->estadoLotes();
                $estadoClass = $clase->claseLotes();

                $anulado = $lote->anulado_en !== null;

                // El lote anulado dejó de ser válido: se muestra como «Anulado»
                // y cuenta en la tarjeta Caducados (mantiene la partición
                // Total = Vigentes + Por caducar + Caducados).
                if ($anulado) {
                    $estado = 'Anulado';
                    $estadoClass = 'expired';
                }

                // Sin filas de inventario el lote no está ubicado: el restante
                // se muestra como «—» en lugar de inventar una cifra.
                $restante = $inventarioSucursal !== null
                    ? (int) $inventarioSucursal->stock
                    : ($lote->inventarios->isEmpty() ? null : (int) $lote->inventarios->sum('stock'));

                return [
                    'id' => $lote->id,
                    'folio' => $lote->folio,
                    'producto' => $nombreProducto,
                    'marca' => $lote->proveedor?->nombre_proveedor
                        ?? $lote->pedido?->proveedor?->nombre_proveedor
                        ?? 'Sin proveedor',
                    'sucursal' => $nombreSucursal,
                    'cantidad' => (int) $lote->stock_lote,
                    'restante' => $restante,
                    'restante_global' => (int) $lote->inventarios->sum('stock'),
                    'anulado' => $anulado,
                    'fecha_entrada' => $lote->entregado_en ? Carbon::parse($lote->entregado_en)->format('Y-m-d') : '-',
                    'fecha_caducidad' => $fechaCaducidad ? $fechaCaducidad->format('Y-m-d') : '-',
                    'estado' => $estado,
                    'estado_class' => $estadoClass,
                ];
            })
            ->values();

        // Contadores sobre el conjunto completo de la sucursal (incluye lo que
        // filtre `buscar`); la tabla muestra solo la categoría de las tarjetas.
        // Cada lote cae en exacto un cubeta: vigente → Vigentes,
        // warning/danger → Por caducar (incluye «Sin fecha»), expired → Caducados.
        $porEstado = fn (string $filtro): Collection => (match ($filtro) {
            'vigentes' => $lotes->filter(fn (array $lote) => $lote['estado_class'] === 'vigente'),
            'por-caducar' => $lotes->filter(fn (array $lote) => in_array($lote['estado_class'], ['warning', 'danger'], true)),
            'caducados' => $lotes->filter(fn (array $lote) => $lote['estado_class'] === 'expired'),
            default => $lotes,
        })->values();

        $totalLotes = $lotes->count();
        $vigentes = $porEstado('vigentes')->count();
        $porCaducar = $porEstado('por-caducar')->count();
        $caducados = $porEstado('caducados')->count();
        $lotes = $porEstado($filtroEstado);

        return view('pages.lotes.index', compact(
            'lotes',
            'sucursales',
            'selectedSucursal',
            'busqueda',
            'filtroEstado',
            'totalLotes',
            'vigentes',
            'porCaducar',
            'caducados',
        ));
    }

    /**
     * Carga los catálogos para registrar un lote sobre un producto existente.
     *
     * El formulario elige el producto en un select y sus presentaciones en
     * otro dependiente, por eso se envían los productos activos con sus
     * presentaciones y el mapa producto → presentaciones.
     *
     * Entrada: query string opcional `sucursal`.
     * Salida: resources/views/pages/lotes/create.blade.php.
     */
    public function create(Request $request): View
    {
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        $pedidos = Pedido::with(['proveedor', 'sucursal'])->orderByDesc('id')->get();
        $proveedores = Proveedor::orderBy('nombre_proveedor')->get();
        $selectedSucursalId = session('active_sucursal_id') ?? $request->query('sucursal') ?? $sucursales->first()?->id;

        $productos = Producto::query()
            ->where('es_activo', true)
            ->with('presentacionesPrecio.presentacion')
            ->orderBy('nombre_producto')
            ->get();

        $presentacionesPorProducto = $productos->mapWithKeys(
            fn (Producto $producto) => [
                $producto->id => $producto->presentacionesPrecio
                    ->map(fn ($presentacion) => [
                        'id' => $presentacion->id_presentacion,
                        'nombre' => $presentacion->presentacion?->presentacion ?? 'Presentación',
                        'unidades' => (int) $presentacion->unidades,
                    ])
                    ->values(),
            ]
        );

        return view('pages.lotes.create', compact('sucursales', 'pedidos', 'proveedores', 'productos', 'presentacionesPorProducto', 'selectedSucursalId'));
    }

    /**
     * Valida y crea el lote vinculado a un producto existente y a la sucursal.
     *
     * El producto y su presentación llegan de los selects del formulario; la
     * presentación debe pertenecer al producto elegido.
     *
     * Entrada: datos del formulario de alta de lote.
     * Salida: redirección a lotes.index con mensaje de resultado.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'folio' => ['required', 'string', 'max:20', 'unique:lotes,folio'],
            'id_pedido' => ['nullable', 'exists:pedidos,id'],
            'id_proveedor' => ['nullable', 'exists:proveedores,id'],
            'sucursal' => ['required', 'exists:sucursales,id'],
            'entregado_en' => ['required', 'date'],
            'fecha_caducidad' => ['required', 'date', 'after_or_equal:entregado_en'],
            'id_producto' => ['required', 'exists:productos,id'],
            'id_presentacion' => [
                'required',
                Rule::exists('presentacion_producto', 'id_presentacion')->where(
                    fn ($query) => $query->where('producto', $request->input('id_producto'))
                ),
            ],
            'stock' => ['required', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($data) {
            $producto = Producto::findOrFail($data['id_producto']);

            $lote = Lote::create([
                'folio' => $data['folio'],
                'stock_lote' => $data['stock'],
                'id_pedido' => $data['id_pedido'] ?? null,
                'id_proveedor' => $data['id_proveedor'] ?? null,
                'id_producto' => $producto->id,
                'id_presentacion' => $data['id_presentacion'],
                'entregado_en' => $data['entregado_en'],
                'fecha_caducidad' => $data['fecha_caducidad'],
                'fecha_de_caducidad' => $data['fecha_caducidad'],
            ]);

            Inventario::updateOrCreate(
                [
                    'id_sucursal' => $data['sucursal'],
                    'id_lote' => $lote->id,
                ],
                ['stock' => $data['stock']]
            );
        });

        return redirect()->route('lotes.index', ['sucursal' => $data['sucursal']])
            ->with('success', 'Lote registrado correctamente.');
    }

    /**
     * Da de baja unidades de un lote en la sucursal visible.
     *
     * Resta del inventario de la sucursal y registra el motivo en `mermas`.
     * `lote.stock_lote` queda intacto como histórico de la entrada.
     *
     * Entrada: lote_id, sucursal, cantidad, motivo y nota del modal de Lotes.
     * Salida: redirección a lotes.index con éxito o con errores de validación.
     */
    public function merma(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'lote_id' => ['required', 'integer', 'exists:lotes,id'],
            'sucursal' => ['required', 'integer', 'exists:sucursales,id'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'motivo' => ['required', Rule::in(['caducado', 'danado', 'otro'])],
            'nota' => ['nullable', 'string', 'max:500', 'required_if:motivo,otro'],
        ], [
            'lote_id.required' => 'Selecciona el lote a dar de baja.',
            'lote_id.exists' => 'El lote indicado ya no existe.',
            'sucursal.exists' => 'La sucursal indicada ya no existe.',
            'cantidad.required' => 'Indica la cantidad a dar de baja.',
            'cantidad.integer' => 'La cantidad debe ser un número entero.',
            'cantidad.min' => 'La cantidad debe ser al menos 1.',
            'motivo.required' => 'Selecciona el motivo de la baja.',
            'motivo.in' => 'El motivo seleccionado no es válido.',
            'nota.required_if' => 'Explica el motivo de la baja.',
        ]);

        $lote = Lote::findOrFail($data['lote_id']);

        DB::transaction(function () use ($lote, $data, $request) {
            $fila = Inventario::query()
                ->where('id_sucursal', $data['sucursal'])
                ->where('id_lote', $lote->id)
                ->lockForUpdate()
                ->first();

            if ($fila === null) {
                throw ValidationException::withMessages([
                    'cantidad' => 'El lote no tiene existencia registrada en esta sucursal.',
                ]);
            }

            if ($fila->stock < $data['cantidad']) {
                throw ValidationException::withMessages([
                    'cantidad' => "La cantidad ({$data['cantidad']}) supera el restante ({$fila->stock}) en esta sucursal.",
                ]);
            }

            $fila->decrement('stock', $data['cantidad']);

            Merma::create([
                'id_sucursal' => $data['sucursal'],
                'id_lote' => $lote->id,
                'cantidad' => $data['cantidad'],
                'motivo' => $data['motivo'],
                'nota' => $data['nota'] ?? null,
                'id_usuario' => $request->user()->id,
            ]);
        });

        return redirect()
            ->route('lotes.index', ['sucursal' => $data['sucursal']])
            ->with('success', "Se dieron de baja {$data['cantidad']} unidades del lote {$lote->folio}.");
    }

    /**
     * Formulario para editar lo mutable de un lote: caducidad y proveedor.
     *
     * Existencias, `stock_lote` y fecha de entrada no se ofrecen: son
     * datos históricos que solo cambian por venta, merma o traspaso.
     *
     * Entrada: lote a editar.
     * Salida: resources/views/pages/lotes/edit.blade.php.
     */
    public function edit(Lote $lote): View
    {
        $proveedores = Proveedor::orderBy('nombre_proveedor')->get();
        $restanteGlobal = (int) Inventario::where('id_lote', $lote->id)->sum('stock');

        return view('pages.lotes.edit', compact('lote', 'proveedores', 'restanteGlobal'));
    }

    /**
     * Actualiza la caducidad y el proveedor del lote.
     *
     * `fecha_de_caducidad` se replica desde el modelo para no romper el
     * espejo de columnas. Entrada: fechas y proveedor del formulario.
     * Salida: redirección a lotes.index con mensaje de resultado.
     */
    public function update(Request $request, Lote $lote): RedirectResponse
    {
        $data = $request->validate([
            'fecha_caducidad' => ['required', 'date', 'after_or_equal:'.$lote->entregado_en->toDateString()],
            'id_proveedor' => ['nullable', 'exists:proveedores,id'],
        ], [
            'fecha_caducidad.required' => 'Indica la fecha de caducidad.',
            'fecha_caducidad.date' => 'La fecha de caducidad no es válida.',
            'fecha_caducidad.after_or_equal' => 'La caducidad no puede ser anterior a la fecha de entrega del lote.',
            'id_proveedor.exists' => 'El proveedor indicado ya no existe.',
        ]);

        $lote->update([
            'fecha_caducidad' => $data['fecha_caducidad'],
            'fecha_de_caducidad' => $data['fecha_caducidad'],
            'id_proveedor' => $data['id_proveedor'] ?? null,
        ]);

        return redirect()
            ->route('lotes.index')
            ->with('success', "Lote {$lote->folio} actualizado correctamente.");
    }

    /**
     * Anula un lote que ya no tiene existencias en ninguna sucursal.
     *
     * Marca `anulado_en` sin borrar el registro: el historial de entradas,
     * ventas y mermas permanece. Solo lotes sin existencias son anulables.
     *
     * Entrada: lote a anular.
     * Salida: redirección con éxito o mensaje de bloqueo.
     */
    public function anular(Lote $lote): RedirectResponse
    {
        [$anulado, $mensaje] = DB::transaction(function () use ($lote) {
            $vigente = Lote::query()->whereKey($lote->id)->lockForUpdate()->firstOrFail();

            if ($vigente->anulado_en !== null) {
                return [false, "El lote {$vigente->folio} ya está anulado."];
            }

            $existencias = (int) Inventario::where('id_lote', $vigente->id)->sum('stock');

            if ($existencias > 0) {
                return [false, "No se puede anular el lote {$vigente->folio}: aún tiene {$existencias} unidades en existencia. Da de baja las unidades primero."];
            }

            $vigente->update(['anulado_en' => now()]);

            return [true, "El lote {$vigente->folio} quedó anulado."];
        });

        return back()->with($anulado ? 'success' : 'error', $mensaje);
    }

    /**
     * Salida de mercancía: descuenta el origen cuando el traspaso se envía.
     *
     * Flujo: PENDIENTE → ENVIADO. Por cada detalle se descuenta la cantidad
     * de `inventario` (origen) y de `lotes.stock_lote`; el destino NO se
     * incrementa: la mercancía queda en tránsito. Es idempotente: solo
     * procede en estado pendiente, así un segundo envío del mismo traspaso
     * devuelve error sin tocar el stock.
     *
     * Entrada: traspaso con sus detalles (`detalle_traspaso.id_lote`).
     * Salida: nada; lanza RuntimeException con mensaje controlado si algo falla.
     *
     * @throws \RuntimeException Cuando el traspaso no existe, no tiene detalles, ya fue procesado o falta stock.
     */
    public function descontarOrigen(Traspaso $traspaso): void
    {
        $this->validarTransicion($traspaso, 'pendiente', 'El traspaso ya fue procesado y no puede descontarse de nuevo.');

        DB::transaction(function () use ($traspaso): void {
            $movimientos = $this->bloquearDisponibilidad($traspaso);

            foreach ($movimientos as [$fila, $lote, $cantidad]) {
                $fila->decrement('stock', $cantidad);
                $lote->decrement('stock_lote', $cantidad);
            }
        });
    }

    /**
     * Recepción de mercancía: incrementa el destino al recibir el traspaso.
     *
     * Flujo: ENVIADO → ACEPTADO (recibido). Por cada detalle se suma la
     * cantidad al `inventario` del destino (creando la fila si no existe) y
     * se conserva el `id_lote` original para trazabilidad
     * (Traspaso → DetalleTraspaso → Lote → Producto). El origen NO se vuelve
     * a descontar. Es idempotente: solo procede en estado enviado.
     *
     * Entrada: traspaso en tránsito con sus detalles.
     * Salida: nada; lanza RuntimeException con mensaje controlado si algo falla.
     *
     * @throws \RuntimeException Cuando el traspaso no existe, no tiene detalles o ya fue recibido.
     */
    public function incrementarDestino(Traspaso $traspaso): void
    {
        $this->validarTransicion($traspaso, 'enviado', 'El traspaso no está en tránsito y no puede recibirse de nuevo.');

        DB::transaction(function () use ($traspaso): void {
            $destinoId = (int) $traspaso->sucursal_b;

            foreach ($traspaso->detalles as $detalle) {
                $lote = Lote::query()->whereKey($detalle->id_lote)->first();

                if ($lote === null || $lote->producto === null) {
                    throw new \RuntimeException("El lote {$detalle->id_lote} del traspaso ya no existe.");
                }

                Inventario::firstOrCreate(
                    ['id_sucursal' => $destinoId, 'id_lote' => $lote->id],
                    ['stock' => 0]
                )->increment('stock', (int) $detalle->cantidad);
            }
        });
    }

    /**
     * Devolución de mercancía: restaura el origen si el traspaso en tránsito
     * se rechaza o se cancela.
     *
     * Solo procede en estado enviado. Suma de vuelta cada cantidad al
     * `inventario` del origen y a `lotes.stock_lote`.
     *
     * Entrada: traspaso en tránsito con sus detalles.
     * Salida: nada; lanza RuntimeException con mensaje controlado si algo falla.
     *
     * @throws \RuntimeException Cuando el traspaso no existe, no tiene detalles o no está en tránsito.
     */
    public function restaurarOrigen(Traspaso $traspaso): void
    {
        $this->validarTransicion($traspaso, 'enviado', 'El traspaso no está en tránsito y su mercancía no puede devolverse.');

        DB::transaction(function () use ($traspaso): void {
            $origenId = (int) $traspaso->sucursal_a;

            foreach ($traspaso->detalles as $detalle) {
                $lote = Lote::query()->whereKey($detalle->id_lote)->lockForUpdate()->first();

                if ($lote === null) {
                    throw new \RuntimeException("El lote {$detalle->id_lote} del traspaso ya no existe.");
                }

                Inventario::firstOrCreate(
                    ['id_sucursal' => $origenId, 'id_lote' => $lote->id],
                    ['stock' => 0]
                )->increment('stock', (int) $detalle->cantidad);

                $lote->increment('stock_lote', (int) $detalle->cantidad);
            }
        });
    }

    /**
     * Valida lo común a toda transición: existencia, detalles y estado.
     *
     * @throws \RuntimeException
     */
    private function validarTransicion(Traspaso $traspaso, string $estadoRequerido, string $mensajeEstado): void
    {
        if (! $traspaso->exists) {
            throw new \RuntimeException('El traspaso no existe.');
        }

        $traspaso->loadMissing(['detalles.lote.producto', 'sucursalOrigen', 'sucursalDestino']);

        if ($traspaso->detalles->isEmpty()) {
            throw new \RuntimeException('El traspaso no tiene detalles de mercancía.');
        }

        if (strtolower((string) $traspaso->estado) !== $estadoRequerido) {
            throw new \RuntimeException($mensajeEstado);
        }
    }

    /**
     * Bloquea y valida la disponibilidad de todos los detalles en origen.
     *
     * Revisa lote por lote (existencia del lote y de su producto, stock en
     * `lotes.stock_lote` y en `inventario` del origen) ANTES de descontar
     * nada, para que la operación se rechace completa si algo falta.
     * Debe llamarse dentro de una transacción.
     *
     * @return array<int, array{Inventario, Lote, int}>
     *
     * @throws \RuntimeException
     */
    private function bloquearDisponibilidad(Traspaso $traspaso): array
    {
        $origenId = (int) $traspaso->sucursal_a;
        $movimientos = [];

        foreach ($traspaso->detalles as $detalle) {
            $cantidad = (int) $detalle->cantidad;

            $lote = Lote::query()->whereKey($detalle->id_lote)->lockForUpdate()->first();

            if ($lote === null || $lote->producto === null) {
                throw new \RuntimeException("El lote {$detalle->id_lote} del traspaso ya no existe.");
            }

            if ((int) $lote->stock_lote < $cantidad) {
                throw new \RuntimeException("Stock insuficiente en el lote {$lote->folio}. Disponible: {$lote->stock_lote} uds, solicitado: {$cantidad} uds.");
            }

            $fila = Inventario::query()
                ->where('id_sucursal', $origenId)
                ->where('id_lote', $lote->id)
                ->lockForUpdate()
                ->first();

            $disponible = (int) ($fila?->stock ?? 0);

            if ($fila === null || $disponible < $cantidad) {
                throw new \RuntimeException("Stock insuficiente en origen para el lote {$lote->folio}. Disponible: {$disponible} uds, solicitado: {$cantidad} uds.");
            }

            $movimientos[] = [$fila, $lote, $cantidad];
        }

        return $movimientos;
    }
}
