<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RolesController extends Controller
{
    /**
     * Store a newly created role.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tipo_rol' => ['required', 'string', 'max:50', 'unique:roles,tipo_rol'],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ]);

        Rol::create($data);

        return redirect()->route('usuarios.index')->with('success', 'Rol registrado correctamente.');
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, Rol $rol): RedirectResponse
    {
        if ($rol->tipo_rol === 'SuperAdmin') {
            return redirect()->route('usuarios.index')->with('error', 'El rol SuperAdmin no se puede editar.');
        }

        $data = $request->validate([
            'tipo_rol' => ['required', 'string', 'max:50', Rule::unique('roles', 'tipo_rol')->ignore($rol->id)],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ]);

        $rol->update($data);

        return redirect()->route('usuarios.index')->with('success', 'Rol actualizado correctamente.');
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Rol $rol): RedirectResponse
    {
        if ($rol->tipo_rol === 'SuperAdmin') {
            return redirect()->route('usuarios.index')->with('error', 'El rol SuperAdmin no se puede eliminar.');
        }

        if ($rol->usuarios()->exists()) {
            return redirect()->route('usuarios.index')->with('error', 'No se puede eliminar el rol porque tiene usuarios asignados.');
        }

        $rol->delete();

        return redirect()->route('usuarios.index')->with('success', 'Rol eliminado correctamente.');
    }
}
