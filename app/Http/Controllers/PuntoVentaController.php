<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Models\Producto;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PuntoVentaController extends Controller
{
    public function index(Request $request): View
    {
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        $selectedSucursalId = $request->query('sucursal') ?? $sucursales->first()?->id;
        $selectedSucursal = $sucursales->firstWhere('id', $selectedSucursalId) ?? $sucursales->first();

        $stockPorProducto = $selectedSucursal
            ? Inventario::stockPorProducto((int) $selectedSucursal->id)
            : [];

        $productos = Producto::query()
            ->where('productos.es_activo', true)
            ->orderBy('productos.nombre_producto')
            ->get(['id', 'codigo_barras', 'nombre_producto', 'descripcion', 'precio'])
            ->each(fn (Producto $producto) => $producto->setAttribute(
                'stock_sucursal',
                (int) ($stockPorProducto[$producto->id] ?? 0)
            ))
            ->filter(fn (Producto $producto) => (int) $producto->getAttribute('stock_sucursal') > 0)
            ->values();

        return view('pages.punto-venta.index', compact(
            'productos',
            'sucursales',
            'selectedSucursal',
        ));
    }
}
