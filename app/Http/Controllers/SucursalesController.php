<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SucursalesController extends Controller
{
    /**
     * Lista las sucursales ordenadas por nombre.
     *
     * Salida: resources/views/pages/sucursales/index.blade.php.
     */
    public function index(): View
    {
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        $totalSucursales = $sucursales->count();
        $sucursalesActivas = $sucursales->where('es_activa', true)->count();

        return view('pages.sucursales.index', compact('sucursales', 'totalSucursales', 'sucursalesActivas'));
    }

    /**
     * Muestra el formulario de alta de sucursales.
     *
     * Salida: resources/views/pages/sucursales/create.blade.php.
     */
    public function create(): View
    {
        return view('pages.sucursales.create', ['sucursal' => null]);
    }

    /**
     * Carga una sucursal existente para editar sus datos operativos.
     *
     * Entrada: sucursal resuelta mediante route model binding.
     * Salida: formulario compartido de alta y edición.
     */
    public function edit(Sucursal $sucursal): View
    {
        return view('pages.sucursales.create', compact('sucursal'));
    }

    /**
     * Valida y registra una sucursal.
     *
     * Entrada: datos del formulario de alta.
     * Salida: redirección al listado de sucursales.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre_sucursal' => ['required', 'string', 'max:150', 'unique:sucursales,nombre_sucursal'],
            'direccion' => ['required', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'correo_contacto' => ['nullable', 'email', 'max:150'],
            'responsable' => ['nullable', 'string', 'max:150'],
            'hora_apertura' => ['required', 'date_format:H:i'],
            'hora_cierre' => ['required', 'date_format:H:i', 'after:hora_apertura'],
            'es_activa' => ['boolean'],
        ]);

        $data['es_activa'] = $request->boolean('es_activa', true);
        Sucursal::create($data);

        return redirect()->route('sucursales.index')->with('success', 'Sucursal agregada correctamente.');
    }

    /**
     * Valida y actualiza una sucursal existente.
     *
     * Entrada: datos del formulario y sucursal resuelta por la ruta.
     * Salida: redirección al listado de sucursales.
     */
    public function update(Request $request, Sucursal $sucursal): RedirectResponse
    {
        $data = $request->validate([
            'nombre_sucursal' => [
                'required',
                'string',
                'max:150',
                Rule::unique('sucursales', 'nombre_sucursal')->ignore($sucursal->id),
            ],
            'direccion' => ['required', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'correo_contacto' => ['nullable', 'email', 'max:150'],
            'responsable' => ['nullable', 'string', 'max:150'],
            'hora_apertura' => ['required', 'date_format:H:i'],
            'hora_cierre' => ['required', 'date_format:H:i', 'after:hora_apertura'],
            'es_activa' => ['boolean'],
        ]);

        $data['es_activa'] = $request->boolean('es_activa');
        $sucursal->update($data);

        return redirect()->route('sucursales.index')->with('success', 'Sucursal actualizada correctamente.');
    }
}
