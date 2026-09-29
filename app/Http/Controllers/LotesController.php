<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Models\Lote;
use App\Models\Pedido;
use App\Models\PresentacionProducto;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Sucursal;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
     * Entrada: query string `sucursal` y `buscar`.
     * Salida: resources/views/pages/lotes/index.blade.php con estados de caducidad.
     */
    public function index(Request $request): View
    {
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        $selectedSucursalId = session('active_sucursal_id') ?? $request->query('sucursal') ?? $sucursales->first()?->id;
        $selectedSucursal = $sucursales->firstWhere('id', $selectedSucursalId) ?? $sucursales->first();
        $busqueda = trim((string) $request->query('buscar', ''));

        $lotes = Lote::with(['producto', 'pedido.proveedor', 'inventarios.sucursal'])
            ->when($selectedSucursal, function ($query, $sucursal) {
                $query->where(function ($subQuery) use ($sucursal) {
                    $subQuery->whereHas('inventarios', function ($inventarioQuery) use ($sucursal) {
                        $inventarioQuery->where('inventario.id_sucursal', $sucursal->id);
                    })->orWhereDoesntHave('inventarios');
                });
            })
            ->when($busqueda !== '', function ($query, $busqueda) {
                $query->where(function ($subQuery) use ($busqueda) {
                    $subQuery->where('folio', 'like', "%{$busqueda}%")
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

                if (! $fechaCaducidad) {
                    $estado = 'Sin fecha';
                    $estadoClass = 'warning';
                } elseif ($fechaCaducidad->isPast()) {
                    $estado = 'Caducado';
                    $estadoClass = 'expired';
                } elseif ($fechaCaducidad->diffInDays(Carbon::now()) <= 30) {
                    $estado = 'Caduca < 30 días';
                    $estadoClass = 'danger';
                } elseif ($fechaCaducidad->diffInDays(Carbon::now()) <= 90) {
                    $estado = 'Caduca < 90 días';
                    $estadoClass = 'warning';
                } else {
                    $estado = 'Vigente';
                    $estadoClass = 'vigente';
                }

                return [
                    'folio' => $lote->folio,
                    'producto' => $nombreProducto,
                    'marca' => $lote->pedido?->proveedor?->nombre_proveedor ?? 'Sin proveedor',
                    'sucursal' => $nombreSucursal,
                    'cantidad' => (int) $lote->stock_lote,
                    'fecha_entrada' => $lote->entregado_en ? Carbon::parse($lote->entregado_en)->format('Y-m-d') : '-',
                    'fecha_caducidad' => $fechaCaducidad ? $fechaCaducidad->format('Y-m-d') : '-',
                    'estado' => $estado,
                    'estado_class' => $estadoClass,
                ];
            })
            ->values();

        $totalLotes = $lotes->count();
        $vigentes = $lotes->filter(fn (array $lote) => $lote['estado_class'] === 'vigente')->count();
        $porCaducar = $lotes->filter(fn (array $lote) => in_array($lote['estado_class'], ['warning', 'danger'], true))->count();
        $caducados = $lotes->filter(fn (array $lote) => $lote['estado_class'] === 'expired')->count();

        return view('pages.lotes.index', compact(
            'lotes',
            'sucursales',
            'selectedSucursal',
            'busqueda',
            'totalLotes',
            'vigentes',
            'porCaducar',
            'caducados',
        ));
    }

    /**
     * Carga los catálogos para registrar un lote y su primer producto.
     *
     * Entrada: query string opcional `sucursal`.
     * Salida: resources/views/pages/lotes/create.blade.php.
     */
    public function create(Request $request): View
    {
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        $pedidos = Pedido::with(['proveedor', 'sucursal'])->orderByDesc('id')->get();
        $proveedores = Proveedor::orderBy('nombre_proveedor')->get();
        $presentaciones = PresentacionProducto::orderBy('presentacion')->get();
        $selectedSucursalId = session('active_sucursal_id') ?? $request->query('sucursal') ?? $sucursales->first()?->id;

        return view('pages.lotes.create', compact('sucursales', 'pedidos', 'proveedores', 'presentaciones', 'selectedSucursalId'));
    }

    /**
     * Valida y crea el producto, su lote y su vínculo con la sucursal.
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
            'codigo_barras' => ['required', 'string', 'max:20'],
            'nombre_producto' => ['required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string'],
            'stock' => ['required', 'integer', 'min:1'],
            'precio' => ['required', 'numeric', 'min:0'],
            'id_presentacion' => ['required', 'exists:presentaciones,id'],
            'es_controlado' => ['boolean'],
        ]);

        DB::transaction(function () use ($data, $request) {
            $producto = Producto::create([
                'codigo_barras' => $data['codigo_barras'],
                'nombre_producto' => $data['nombre_producto'],
                'descripcion' => $data['descripcion'] ?? '',
                'stock' => $data['stock'],
                'precio' => $data['precio'],
                'id_presentacion' => $data['id_presentacion'],
                'es_controlado' => $request->boolean('es_controlado', false),
                'entregado_en' => $data['entregado_en'],
                'es_activo' => true,
            ]);

            $lote = Lote::create([
                'folio' => $data['folio'],
                'stock_lote' => $data['stock'],
                'id_pedido' => $data['id_pedido'] ?? null,
                'id_producto' => $producto->id,
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
}
