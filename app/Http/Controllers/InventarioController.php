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
    public function index(Request $request): View
    {
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();

        $selectedSucursalId = $request->query('sucursal') ?? $sucursales->first()?->id;
        $selectedSucursal = $sucursales->firstWhere('id', $selectedSucursalId) ?? $sucursales->first();

        $busqueda = trim((string) $request->query('buscar', ''));

        $productos = Producto::query()
            ->with('sucursales')
            ->when($selectedSucursal, function ($query, $sucursal) {
                $query->whereHas('sucursales', function ($subQuery) use ($sucursal) {
                    $subQuery->where('sucursales.id', $sucursal->id);
                });
            })
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

    public function create(Request $request): View
    {
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        $selectedSucursalId = $request->query('sucursal') ?? $sucursales->first()?->id;
        $presentaciones = PresentacionProducto::orderBy('presentacion')->get();
        $lotes = Lote::with('proveedor')->orderBy('folio')->get();

        return view('pages.inventario.create', compact('sucursales', 'selectedSucursalId', 'presentaciones', 'lotes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'sucursal' => ['required', 'exists:sucursales,id'],
            'codigo_barras' => ['required', 'string', 'max:255'],
            'nombre_producto' => ['required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string'],
            'stock' => ['required', 'integer', 'min:0'],
            'precio' => ['required', 'numeric', 'min:0'],
            'id_lote' => ['nullable', 'exists:lotes,id'],
            'id_presentacion' => ['nullable', 'exists:presentacion_productos,id'],
            'es_controlado' => ['boolean'],
        ]);

        $producto = Producto::create([
            'codigo_barras' => $data['codigo_barras'],
            'nombre_producto' => $data['nombre_producto'],
            'descripcion' => $data['descripcion'] ?? '',
            'stock' => $data['stock'],
            'precio' => $data['precio'],
            'id_lote' => $data['id_lote'] ?? 1,
            'id_presentacion' => $data['id_presentacion'] ?? 1,
            'es_controlado' => $request->boolean('es_controlado', false),
            'es_activo' => true,
        ]);

        $producto->sucursales()->syncWithoutDetaching([$data['sucursal']]);

        return redirect()->route('inventario.index', ['sucursal' => $data['sucursal']])
            ->with('success', 'Producto agregado correctamente al inventario.');
    }
}
