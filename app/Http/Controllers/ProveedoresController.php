<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProveedoresController extends Controller
{
    /**
     * Lista los proveedores ordenados por nombre.
     *
     * Salida: resources/views/pages/proveedores/index.blade.php.
     */
    public function index(): View
    {
        $proveedores = Proveedor::orderBy('nombre_proveedor')->get();
        $totalProveedores = $proveedores->count();
        $proveedoresConCorreo = $proveedores->filter(fn ($proveedor) => filled($proveedor->correo))->count();
        $proveedoresSinTelefono = $proveedores->filter(fn ($proveedor) => blank($proveedor->telefono))->count();

        return view('pages.proveedores.index', compact(
            'proveedores',
            'totalProveedores',
            'proveedoresConCorreo',
            'proveedoresSinTelefono'
        ));
    }

    /**
     * Muestra el formulario de alta de proveedores.
     *
     * Salida: resources/views/pages/proveedores/create.blade.php.
     */
    public function create(): View
    {
        return view('pages.proveedores.create');
    }

    /**
     * Valida y registra un proveedor.
     *
     * Entrada: datos del formulario de alta.
     * Salida: redirección al listado de proveedores.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre_proveedor' => ['required', 'string', 'max:20'],
            'direccion' => ['required', 'string'],
            'unidad_entrega' => ['required', 'string', 'max:20'],
            'telefono' => ['required', 'string', 'max:20'],
            'correo' => ['required', 'email', 'max:30'],
        ]);

        Proveedor::create($data);

        return redirect()->route('proveedores.index')->with('success', 'Proveedor agregado correctamente.');
    }

    /**
     * Carga un proveedor existente para editar sus datos.
     *
     * Entrada: proveedor resuelto mediante route model binding.
     * Salida: resources/views/pages/proveedores/edit.blade.php.
     */
    public function edit(Proveedor $proveedor): View
    {
        return view('pages.proveedores.edit', compact('proveedor'));
    }

    /**
     * Valida y actualiza un proveedor existente.
     *
     * Entrada: datos del formulario y proveedor resuelto por la ruta.
     * Salida: redirección al listado de proveedores.
     */
    public function update(Request $request, Proveedor $proveedor): RedirectResponse
    {
        $data = $request->validate([
            'nombre_proveedor' => ['required', 'string', 'max:20'],
            'direccion' => ['required', 'string'],
            'unidad_entrega' => ['required', 'string', 'max:20'],
            'telefono' => ['required', 'string', 'max:20'],
            'correo' => ['required', 'email', 'max:30'],
        ]);

        $proveedor->update($data);

        return redirect()->route('proveedores.index')->with('success', 'Proveedor actualizado correctamente.');
    }
}
