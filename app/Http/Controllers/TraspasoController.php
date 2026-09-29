<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use App\Models\Traspaso;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TraspasoController extends Controller
{
    /**
     * Redirige al listado unificado de entradas filtrado por traspasos.
     *
     * Entrada: query string opcional `sucursal`.
     * Salida: redirección a entradas-de-almacen.index con tipo=traspasos.
     */
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('entradas-de-almacen.index', array_filter([
            'sucursal' => $request->query('sucursal'),
            'tipo' => 'traspasos',
        ]));
    }

    /**
     * Muestra el formulario de alta de traspasos entre sucursales.
     *
     * Entrada: query string opcional `sucursal` (destino preseleccionado).
     * Salida: resources/views/pages/entradas/create-traspaso.blade.php.
     */
    public function create(Request $request): View
    {
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        $selectedSucursalId = session('active_sucursal_id') ?? $request->query('sucursal') ?? $sucursales->first()?->id;

        return view('pages.entradas.create-traspaso', compact('sucursales', 'selectedSucursalId'));
    }

    /**
     * Valida y registra un traspaso entre dos sucursales distintas.
     *
     * Entrada: sucursal origen, sucursal destino y estado inicial.
     * Salida: redirección al listado de entradas filtrado por traspasos.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'sucursal_a' => ['required', 'exists:sucursales,id', 'different:sucursal_b'],
            'sucursal_b' => ['required', 'exists:sucursales,id', 'different:sucursal_a'],
            'estado' => ['nullable', 'string', 'max:20'],
        ]);

        Traspaso::create([
            'sucursal_a' => $data['sucursal_a'],
            'sucursal_b' => $data['sucursal_b'],
            'pedido_por' => $request->user()->id,
            'estado' => $data['estado'] ?? 'pendiente',
        ]);

        return redirect()->route('entradas-de-almacen.index', [
            'sucursal' => $data['sucursal_b'],
            'tipo' => 'traspasos',
        ])->with('success', 'Traspaso registrado correctamente.');
    }
}
