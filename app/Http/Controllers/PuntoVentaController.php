<?php

namespace App\Http\Controllers;

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

        $productos = Producto::query()
            ->where('es_activo', true)
            ->where('stock', '>', 0)
            ->when($selectedSucursal, function ($query, $sucursal) {
                $query->whereHas('sucursales', function ($subQuery) use ($sucursal) {
                    $subQuery->where('sucursales.id', $sucursal->id);
                });
            })
            ->orderBy('nombre_producto')
            ->get(['id', 'codigo_barras', 'nombre_producto', 'descripcion', 'stock', 'precio']);

        return view('pages.punto-venta.index', compact(
            'productos',
            'sucursales',
            'selectedSucursal',
        ));
    }
}