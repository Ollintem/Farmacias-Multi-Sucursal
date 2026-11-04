<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Inventario;
use App\Models\Lote;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Sucursal;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CompraController extends Controller
{
    /**
     * Redirige al listado unificado de entradas filtrado por compras.
     *
     * Entrada: query string opcional `sucursal`.
     * Salida: redirección a entradas-de-almacen.index con tipo=compras.
     */
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('entradas-de-almacen.index', array_filter([
            'sucursal' => $request->query('sucursal'),
            'tipo' => 'compras',
        ]));
    }

    /**
     * Muestra el formulario de alta de compras a proveedores.
     *
     * Entrada: query string opcional `sucursal` (destino preseleccionado).
     * Salida: resources/views/pages/entradas/create-compra.blade.php.
     */
    public function create(Request $request): View
    {
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        $proveedores = Proveedor::orderBy('nombre_proveedor')->get();
        $productos = Producto::query()
            ->where('es_activo', true)
            ->with('presentacionesPrecio.presentacion')
            ->orderBy('nombre_producto')
            ->get();

        $presentacionPorProducto = $productos->mapWithKeys(function (Producto $producto): array {
            $presentacion = $producto->presentacionParaLote();

            return [
                $producto->id => $presentacion === null ? null : [
                    'id' => $presentacion->id_presentacion,
                    'nombre' => $presentacion->presentacion?->presentacion ?? 'Presentación',
                    'unidades' => (int) $presentacion->unidades,
                ],
            ];
        });

        $selectedSucursalId = session('active_sucursal_id') ?? $request->query('sucursal') ?? $sucursales->first()?->id;

        return view('pages.entradas.create-compra', compact(
            'sucursales',
            'proveedores',
            'productos',
            'presentacionPorProducto',
            'selectedSucursalId',
        ));
    }

    /**
     * Registra una compra con sus lotes e incorpora el inventario.
     *
     * Cada lote guarda producto, cantidad, fecha de caducidad y fecha de
     * entrada. La cantidad se captura en la presentación principal del
     * producto y se convierte a unidades base, igual que en inventario.
     * El usuario activo queda registrado como quien recibe la compra y el
     * inventario de la sucursal se actualiza en la misma transacción.
     *
     * Entrada: proveedor, sucursal y listado de lotes comprados.
     * Salida: redirección al listado de entradas filtrado por compras.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->normalizarLotes($request);

        $data = $request->validate([
            'id_proveedor' => ['required', 'exists:proveedores,id'],
            'id_sucursal' => ['required', 'exists:sucursales,id'],
            'lotes' => ['required', 'array', 'min:1'],
            'lotes.*.id_producto' => ['required', 'exists:productos,id'],
            'lotes.*.cantidad' => ['required', 'integer', 'min:1'],
            'lotes.*.fecha_caducidad' => ['required', 'date'],
            'lotes.*.fecha_entrada' => ['required', 'date'],
        ], [
            'id_proveedor.required' => 'Selecciona el proveedor de la compra.',
            'id_sucursal.required' => 'Selecciona la sucursal de destino.',
            'lotes.required' => 'Agrega al menos un lote a la compra.',
            'lotes.min' => 'Agrega al menos un lote a la compra.',
            'lotes.*.id_producto.required' => 'Selecciona el producto de cada lote.',
            'lotes.*.cantidad.required' => 'Indica la cantidad adquirida de cada lote.',
            'lotes.*.cantidad.min' => 'La cantidad de cada lote debe ser al menos 1.',
            'lotes.*.fecha_caducidad.required' => 'Indica la fecha de caducidad de cada lote.',
            'lotes.*.fecha_entrada.required' => 'Indica la fecha de entrada de cada lote.',
        ]);

        $this->validarFechasDeLotes($data['lotes']);

        $compra = DB::transaction(function () use ($data, $request): Compra {
            $compra = Compra::create([
                'id_proveedor' => $data['id_proveedor'],
                'id_sucursal' => $data['id_sucursal'],
                'pedido_por' => $request->user()->id,
                'recibido_por' => $request->user()->id,
                'entregado_en' => now(),
            ]);

            foreach (array_values($data['lotes']) as $indice => $fila) {
                $producto = Producto::findOrFail($fila['id_producto']);
                $presentacion = $producto->presentacionParaLote();
                $idPresentacion = $presentacion?->id_presentacion;
                $unidades = $presentacion === null
                    ? 1
                    : max(1, (int) $presentacion->unidades);
                $cantidadBase = (int) $fila['cantidad'] * $unidades;

                $lote = Lote::create([
                    'folio' => sprintf('C-%d-%d', $compra->id, $indice + 1),
                    'stock_lote' => $cantidadBase,
                    'id_compra' => $compra->id,
                    'id_proveedor' => $compra->id_proveedor,
                    'id_producto' => $producto->id,
                    'id_presentacion' => $idPresentacion,
                    'entregado_en' => $fila['fecha_entrada'],
                    'fecha_caducidad' => $fila['fecha_caducidad'],
                    'fecha_de_caducidad' => $fila['fecha_caducidad'],
                ]);

                $inventario = Inventario::firstOrCreate([
                    'id_sucursal' => $data['id_sucursal'],
                    'id_lote' => $lote->id,
                ]);

                if ($inventario->wasRecentlyCreated) {
                    $inventario->fijarUnidadesIndividuales($cantidadBase);
                }

                $producto->sucursales()->syncWithoutDetaching([$data['id_sucursal']]);
            }

            return $compra;
        });

        return redirect()->route('entradas-de-almacen.index', [
            'sucursal' => $data['id_sucursal'],
            'tipo' => 'compras',
        ])->with('success', "Compra #{$compra->id} registrada y recibida correctamente.");
    }

    /**
     * Acepta alias de los campos de lote usados por formularios previos.
     *
     * Entrada: petición con `lotes` (o `productos`) y campos equivalentes.
     * Salida: petición normalizada con `lotes.*` y los nombres canónicos.
     */
    private function normalizarLotes(Request $request): void
    {
        $lotes = $request->input('lotes', $request->input('productos'));

        if (! is_array($lotes)) {
            return;
        }

        $fechaEntradaComun = $request->input('fecha_entrada') ?? $request->input('entregado_en');
        $fechaCaducidadComun = $request->input('fecha_caducidad') ?? $request->input('fecha_de_caducidad');

        $normalizados = array_map(function (mixed $fila) use ($fechaEntradaComun, $fechaCaducidadComun): mixed {
            if (! is_array($fila)) {
                return $fila;
            }

            $fila['id_producto'] ??= $fila['id'] ?? $fila['producto'] ?? null;
            $fila['cantidad'] ??= $fila['stock'] ?? null;
            $fila['fecha_entrada'] ??= $fila['entregado_en'] ?? $fechaEntradaComun;
            $fila['fecha_caducidad'] ??= $fila['fecha_de_caducidad'] ?? $fechaCaducidadComun;

            return $fila;
        }, $lotes);

        $request->merge(['lotes' => $normalizados]);
    }

    /**
     * Valida que la caducidad de cada lote no sea anterior a su entrada.
     *
     * @param  array<int, array<string, mixed>>  $lotes
     *
     * @throws ValidationException
     */
    private function validarFechasDeLotes(array $lotes): void
    {
        foreach (array_values($lotes) as $indice => $fila) {
            $entrada = Carbon::parse($fila['fecha_entrada']);
            $caducidad = Carbon::parse($fila['fecha_caducidad']);

            if ($caducidad->lt($entrada)) {
                throw ValidationException::withMessages([
                    "lotes.{$indice}.fecha_caducidad" => 'La caducidad no puede ser anterior a la fecha de entrada del lote.',
                ]);
            }
        }
    }
}
