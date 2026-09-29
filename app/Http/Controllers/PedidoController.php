<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Proveedor;
use App\Models\Sucursal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PedidoController extends Controller
{
    /**
     * Redirige al listado unificado de entradas filtrado por pedidos.
     *
     * Entrada: query string opcional `sucursal`.
     * Salida: redirección a entradas-de-almacen.index con tipo=pedidos.
     */
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('entradas-de-almacen.index', array_filter([
            'sucursal' => $request->query('sucursal'),
            'tipo' => 'pedidos',
        ]));
    }

    /**
     * Muestra el formulario de alta de pedidos a proveedores.
     *
     * Entrada: query string opcional `sucursal` (destino preseleccionado).
     * Salida: resources/views/pages/entradas/create-pedido.blade.php.
     */
    public function create(Request $request): View
    {
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        $proveedores = Proveedor::orderBy('nombre_proveedor')->get();
        $selectedSucursalId = session('active_sucursal_id') ?? $request->query('sucursal') ?? $sucursales->first()?->id;

        return view('pages.entradas.create-pedido', compact('sucursales', 'proveedores', 'selectedSucursalId'));
    }

    /**
     * Valida y registra un pedido a proveedor en estado pendiente.
     *
     * Entrada: proveedor, sucursal destino y fecha de entrega opcional.
     * Salida: redirección al listado de entradas filtrado por pedidos.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_proveedor' => ['required', 'exists:proveedores,id'],
            'id_sucursal' => ['required', 'exists:sucursales,id'],
            'estado' => ['nullable', 'string', 'max:30'],
            'entregado_en' => ['nullable', 'date'],
        ]);

        Pedido::create([
            'id_proveedor' => $data['id_proveedor'],
            'id_sucursal' => $data['id_sucursal'],
            'pedido_por' => $request->user()->id,
            'estado' => $data['estado'] ?? 'pendiente',
            'entregado_en' => $data['entregado_en'] ?? null,
        ]);

        return redirect()->route('entradas-de-almacen.index', [
            'sucursal' => $data['id_sucursal'],
            'tipo' => 'pedidos',
        ])->with('success', 'Pedido registrado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
