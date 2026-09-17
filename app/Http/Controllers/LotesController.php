<?php

namespace App\Http\Controllers;

use App\Models\Lote;
use Carbon\Carbon;
use Illuminate\View\View;

class LotesController extends Controller
{
    public function index(): View
    {
        $lotes = Lote::with(['productos.sucursales', 'proveedor'])
            ->orderBy('fecha_caducidad')
            ->get()
            ->map(function ($lote) {
                $producto = $lote->productos->first();
                $sucursal = $producto?->sucursales->first();
                $nombreProducto = $producto?->nombre_producto ?? 'Producto sin nombre';
                $nombreSucursal = $sucursal?->nombre_sucursal ?? 'Sin sucursal';
                $fechaCaducidad = $lote->fecha_caducidad ? Carbon::parse($lote->fecha_caducidad) : null;

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
                    'marca' => $lote->proveedor?->nombre_proveedor ?? 'Sin proveedor',
                    'sucursal' => $nombreSucursal,
                    'cantidad' => $producto?->stock ?? 0,
                    'fecha_entrada' => $lote->entregado_en ? Carbon::parse($lote->entregado_en)->format('Y-m-d') : '-',
                    'fecha_caducidad' => $fechaCaducidad ? $fechaCaducidad->format('Y-m-d') : '-',
                    'estado' => $estado,
                    'estado_class' => $estadoClass,
                ];
            });

        return view('lotes.index', compact('lotes'));
    }
}
