<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Models\Lote;
use App\Models\Merma;
use App\Models\Proveedor;
use App\Models\Sucursal;
use App\Models\Traspaso;
use App\Support\EstadoCaducidad;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        /** @var Sucursal|null $selectedSucursal */
        $selectedSucursal = $sucursales->firstWhere('id', $selectedSucursalId) ?? $sucursales->first();
        $busqueda = trim((string) $request->query('buscar', ''));
        $filtroEstado = $request->query('estado', 'todos');

        if (! is_string($filtroEstado) || ! in_array($filtroEstado, ['todos', 'vigentes', 'por-caducar', 'caducados'], true)) {
            $filtroEstado = 'todos';
        }

        $lotes = Lote::with(['producto.presentacionesPrecio', 'presentacion', 'compra.proveedor', 'proveedor', 'inventarios.sucursal'])
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
                        ->orWhereHas('compra.proveedor', function ($proveedorQuery) use ($busqueda) {
                            $proveedorQuery->where('nombre_proveedor', 'like', "%{$busqueda}%");
                        })
                        ->orWhereHas('producto', function ($productoQuery) use ($busqueda) {
                            $productoQuery->where('nombre_producto', 'like', "%{$busqueda}%");
                        });
                });
            })
            ->orderBy('fecha_caducidad')
            ->get()
            ->map(function (Lote $lote) use ($selectedSucursal): array {
                $producto = $lote->producto;
                $inventarioSucursal = $selectedSucursal
                    ? $lote->inventarios->firstWhere('id_sucursal', $selectedSucursal->id)
                    : $lote->inventarios->first();
                $nombreProducto = $producto !== null ? $producto->nombre_producto : 'Producto sin nombre';
                $nombreSucursal = $inventarioSucursal?->sucursal->nombre_sucursal ?? $selectedSucursal->nombre_sucursal ?? 'Sin sucursal';
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
                    ? (int) $inventarioSucursal->stock_individual
                    : ($lote->inventarios->isEmpty() ? null : (int) $lote->inventarios->sum('stock_individual'));

                // Cantidades en la unidad del lote (cajas/sueltas) con su
                // equivalencia en unidades base entre paréntesis.
                $partesCantidad = $lote->partesDeUnidades((int) $lote->stock_lote);

                return [
                    'id' => $lote->id,
                    'folio' => $lote->folio,
                    'producto' => $nombreProducto,
                    'marca' => $lote->id_proveedor !== null
                        ? $lote->proveedor->nombre_proveedor
                        : ($lote->id_compra !== null
                            ? $lote->compra?->proveedor?->nombre_proveedor
                            : 'Sin proveedor'),
                    'sucursal' => $nombreSucursal,
                    'cantidad' => (int) $lote->stock_lote,
                    'cantidad_texto' => $partesCantidad,
                    'presentacion' => $partesCantidad['etiqueta'],
                    'por_caja' => $lote->unidadesPresentacion() ?? 0,
                    'restante' => $restante,
                    'restante_texto' => $restante === null ? null : $lote->partesDeUnidades($restante),
                    'restante_global' => (int) $lote->inventarios->sum('stock_individual'),
                    'anulado' => $anulado,
                    'fecha_entrada' => $lote->entregado_en->format('Y-m-d'),
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
        $vigentesLotes = $lotes->filter(fn (array $lote): bool => $lote['estado_class'] === 'vigente');
        $porCaducarLotes = $lotes->filter(fn (array $lote): bool => in_array($lote['estado_class'], ['warning', 'danger'], true));
        $caducadosLotes = $lotes->filter(fn (array $lote): bool => $lote['estado_class'] === 'expired');

        $totalLotes = $lotes->count();
        $vigentes = $vigentesLotes->count();
        $porCaducar = $porCaducarLotes->count();
        $caducados = $caducadosLotes->count();

        $lotes = match ($filtroEstado) {
            'vigentes' => $vigentesLotes,
            'por-caducar' => $porCaducarLotes,
            'caducados' => $caducadosLotes,
            default => $lotes,
        };

        $lotes = $lotes->values();

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
     * Da de baja unidades de un lote en la sucursal visible.
     *
     * Resta del inventario de la sucursal y registra el motivo en `mermas`.
     * `lote.stock_lote` queda intacto como histórico de la entrada.
     *
     * El modal captura en la unidad del lote: lotes en caja se reciben como
     * `cantidad_cajas` + `cantidad_sueltas` (convertidos aquí a unidades
     * base) y lotes unitarios como `cantidad` en unidades base.
     *
     * Entrada: lote_id, sucursal, cantidad, motivo y nota del modal de Lotes.
     * Salida: redirección a lotes.index con éxito o con errores de validación.
     */
    public function merma(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'lote_id' => ['required', 'integer', 'exists:lotes,id'],
            'sucursal' => ['required', 'integer', 'exists:sucursales,id'],
            'cantidad' => ['nullable', 'integer', 'min:1', 'required_without:cantidad_cajas'],
            'cantidad_cajas' => ['nullable', 'integer', 'min:0'],
            'cantidad_sueltas' => ['nullable', 'integer', 'min:0'],
            'motivo' => ['required', Rule::in(['caducado', 'danado', 'otro'])],
            'nota' => ['nullable', 'string', 'max:500', 'required_if:motivo,otro'],
        ], [
            'lote_id.required' => 'Selecciona el lote a dar de baja.',
            'lote_id.exists' => 'El lote indicado ya no existe.',
            'sucursal.exists' => 'La sucursal indicada ya no existe.',
            'cantidad.required_without' => 'Indica las cajas o las unidades a dar de baja.',
            'cantidad.integer' => 'La cantidad debe ser un número entero.',
            'cantidad.min' => 'La cantidad debe ser al menos 1.',
            'cantidad_cajas.integer' => 'Las cajas deben ser un número entero.',
            'cantidad_cajas.min' => 'Las cajas no pueden ser negativas.',
            'cantidad_sueltas.integer' => 'Las unidades sueltas deben ser un número entero.',
            'cantidad_sueltas.min' => 'Las unidades sueltas no pueden ser negativas.',
            'motivo.required' => 'Selecciona el motivo de la baja.',
            'motivo.in' => 'El motivo seleccionado no es válido.',
            'nota.required_if' => 'Explica el motivo de la baja.',
        ]);

        $lote = Lote::findOrFail($data['lote_id']);
        $porCaja = $lote->unidadesPresentacion();

        $unidades = $porCaja === null
            ? (int) ($data['cantidad'] ?? 0)
            : (int) ($data['cantidad_cajas'] ?? 0) * $porCaja + (int) ($data['cantidad_sueltas'] ?? 0);

        if ($unidades < 1) {
            throw ValidationException::withMessages([
                'cantidad' => $porCaja === null
                    ? 'Indica la cantidad a dar de baja.'
                    : 'Indica las cajas o las unidades sueltas a dar de baja.',
            ]);
        }

        DB::transaction(function () use ($lote, $data, $unidades, $request) {
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

            if ($fila->stock_individual < $unidades) {
                throw ValidationException::withMessages([
                    'cantidad' => "La cantidad ({$unidades}) supera el restante ({$fila->stock_individual}) en esta sucursal.",
                ]);
            }

            $fila->ajustarUnidadesIndividuales(-$unidades);

            Merma::create([
                'id_sucursal' => $data['sucursal'],
                'id_lote' => $lote->id,
                'cantidad' => $unidades,
                'motivo' => $data['motivo'],
                'nota' => $data['nota'] ?? null,
                'id_usuario' => $request->user()->id,
            ]);
        });

        return redirect()
            ->route('lotes.index', ['sucursal' => $data['sucursal']])
            ->with('success', "Se dieron de baja {$unidades} unidades del lote {$lote->folio}.");
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
        $restanteGlobal = (int) Inventario::where('id_lote', $lote->id)->sum('stock_individual');

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

            $existencias = (int) Inventario::where('id_lote', $vigente->id)->sum('stock_individual');

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
                $fila->ajustarUnidadesIndividuales(-$cantidad);
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

                $destino = Inventario::firstOrCreate(
                    ['id_sucursal' => $destinoId, 'id_lote' => $lote->id]
                );

                $destino->ajustarUnidadesIndividuales((int) $detalle->cantidad);

                // La recepción da de alta el producto en la sucursal receptora.
                $lote->producto->sucursales()->syncWithoutDetaching([$destinoId]);
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

                $origen = Inventario::firstOrCreate(
                    ['id_sucursal' => $origenId, 'id_lote' => $lote->id]
                );

                $origen->ajustarUnidadesIndividuales((int) $detalle->cantidad);

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

            $disponible = (int) ($fila?->stock_individual ?? 0);

            if ($fila === null || $disponible < $cantidad) {
                throw new \RuntimeException("Stock insuficiente en origen para el lote {$lote->folio}. Disponible: {$disponible} uds, solicitado: {$cantidad} uds.");
            }

            $movimientos[] = [$fila, $lote, $cantidad];
        }

        return $movimientos;
    }
}
