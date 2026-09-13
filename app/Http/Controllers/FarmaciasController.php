<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Farmacia;

class FarmaciasController extends Controller
{
    /**
     * Mostrar todas las farmacias.
     */
    public function index()
    {
        $farmacias = Farmacia::all();
        return view('farmacias.index', compact('farmacias'));
    }

    /**
     * Mostrar formulario de creación.
     */
    public function create()
    {
        return view('farmacias.create');
    }

    /**
     * Guardar nueva farmacia.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
        ]);

        Farmacia::create($request->all());

        return redirect()->route('farmacias.index')
                         ->with('success', 'Farmacia creada correctamente.');
    }

    /**
     * Mostrar una farmacia específica.
     */
    public function show(string $id)
    {
        $farmacia = Farmacia::findOrFail($id);
        return view('farmacias.show', compact('farmacia'));
    }

    /**
     * Mostrar formulario de edición.
     */
    public function edit(string $id)
    {
        $farmacia = Farmacia::findOrFail($id);
        return view('farmacias.edit', compact('farmacia'));
    }

    /**
     * Actualizar farmacia.
     */
    public function update(Request $request, string $id)
    {
        $farmacia = Farmacia::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
        ]);

        $farmacia->update($request->all());

        return redirect()->route('farmacias.index')
                         ->with('success', 'Farmacia actualizada correctamente.');
    }

    /**
     * Eliminar farmacia.
     */
    public function destroy(string $id)
    {
        $farmacia = Farmacia::findOrFail($id);
        $farmacia->delete();

        return redirect()->route('farmacias.index')
                         ->with('success', 'Farmacia eliminada correctamente.');
    }
}
