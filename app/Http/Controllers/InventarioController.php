<?php

namespace App\Http\Controllers;

use App\Models\Lote;
use App\Models\PresentacionProducto;
use App\Models\Producto;
use App\Models\Sucursal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventarioController extends Controller
{
    /**
     * Muestra el inventario filtrado por sucursal y texto de búsqueda.
     *
     * Entrada: query string `sucursal` y `buscar`.
     * Salida: resources/views/pages/inventario/index.blade.php.
     */
    public function index(Request $request): View
    {
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();

        $selectedSucursalId = session('active_sucursal_id') ?? $request->query('sucursal') ?? $sucursales->first()?->id;
        $selectedSucursal = $sucursales->firstWhere('id', $selectedSucursalId) ?? $sucursales->first();

        $busqueda = trim((string) $request->query('buscar', ''));

        $productos = Producto::query()
            ->with('sucursales')
            ->activeSucursal()
            ->when($busqueda !== '', function ($query, $busqueda) {
                $query->where(function ($subQuery) use ($busqueda) {
                    $subQuery->where('codigo_barras', 'like', "%{$busqueda}%")
                        ->orWhere('id', $busqueda)
                        ->orWhere('nombre_producto', 'like', "%{$busqueda}%");
                });
            })
            ->orderBy('nombre_producto')
            ->get();

        $stockTotal = (int) $productos->sum('stock');
        $stockCritico = $productos->filter(fn (Producto $producto) => $producto->stock <= 15)->count();
        $valorTotal = $productos->sum(fn (Producto $producto) => (float) $producto->stock * (float) $producto->precio);

        return view('pages.inventario.index', compact(
            'sucursales',
            'selectedSucursal',
            'productos',
            'stockTotal',
            'stockCritico',
            'valorTotal',
            'busqueda',
        ));
    }

    /**
     * Carga los catálogos necesarios para registrar un producto.
     *
     * Entrada: query string opcional `sucursal`.
     * Salida: resources/views/pages/inventario/create.blade.php.
     */
    public function create(Request $request): View
    {
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        $selectedSucursalId = session('active_sucursal_id') ?? $request->query('sucursal') ?? $sucursales->first()?->id;
        $presentaciones = PresentacionProducto::orderBy('presentacion')->get();
        $lotes = Lote::with('proveedor')->orderBy('folio')->get();

        return view('pages.inventario.create', compact('sucursales', 'selectedSucursalId', 'presentaciones', 'lotes'));
    }

    /**
     * Valida, crea el producto y lo vincula con la sucursal seleccionada.
     *
     * Entrada: datos del formulario de alta de inventario.
     * Salida: redirección a inventario.index con mensaje de resultado.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'sucursal' => ['required', 'exists:sucursales,id'],
            'codigo_barras' => ['required', 'string', 'max:255'],
            'nombre_producto' => ['required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string'],
            'stock' => ['required', 'integer', 'min:0'],
            'precio' => ['required', 'numeric', 'min:0'],
            'id_lote' => ['required', 'exists:lotes,id'],
            'id_presentacion' => ['required', 'exists:presentaciones,id'],
            'es_controlado' => ['boolean'],
        ]);

        $producto = Producto::create([
            'codigo_barras' => $data['codigo_barras'],
            'nombre_producto' => $data['nombre_producto'],
            'descripcion' => $data['descripcion'] ?? '',
            'stock' => $data['stock'],
            'precio' => $data['precio'],

            'id_lote' => $data['id_lote'],
            'id_presentacion' => $data['id_presentacion'],

            'id_lote' => $data['id_lote'] ?? null,
            'id_presentacion' => $data['id_presentacion'] ?? null,

            'es_controlado' => $request->boolean('es_controlado', false),
            'es_activo' => true,
        ]);

        return redirect()->route('inventario.index', ['sucursal' => $data['sucursal']])
            ->with('success', 'Producto agregado correctamente al inventario.');
    }
}
