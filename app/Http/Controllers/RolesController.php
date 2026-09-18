<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RolesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $roles = Rol::withCount('usuarios')->orderBy('tipo_rol')->get();

        return view('pages.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('pages.roles.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tipo_rol' => ['required', 'string', 'max:50', 'unique:roles,tipo_rol'],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ]);

        Rol::create($data);

        return redirect()->route('roles.index')->with('success', 'Rol registrado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Rol $rol): View
    {
        $rol->loadCount('usuarios');

        return view('pages.roles.edit', [
            'rol' => $rol,
            'modo' => 'ver',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Rol $rol): View|RedirectResponse
    {
        if ($rol->tipo_rol === 'SuperAdmin') {
            return redirect()->route('roles.index')->with('error', 'El rol SuperAdmin no se puede editar.');
        }

        return view('pages.roles.edit', [
            'rol' => $rol,
            'modo' => 'editar',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Rol $rol): RedirectResponse
    {
        if ($rol->tipo_rol === 'SuperAdmin') {
            return redirect()->route('roles.index')->with('error', 'El rol SuperAdmin no se puede editar.');
        }

        $data = $request->validate([
            'tipo_rol' => ['required', 'string', 'max:50', Rule::unique('roles', 'tipo_rol')->ignore($rol->id)],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ]);

        $rol->update($data);

        return redirect()->route('roles.index')->with('success', 'Rol actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Rol $rol): RedirectResponse
    {
        if ($rol->tipo_rol === 'SuperAdmin') {
            return redirect()->route('roles.index')->with('error', 'El rol SuperAdmin no se puede eliminar.');
        }

        if ($rol->usuarios()->exists()) {
            return redirect()->route('roles.index')->with('error', 'No se puede eliminar el rol porque tiene usuarios asignados.');
        }

        $rol->delete();

        return redirect()->route('roles.index')->with('success', 'Rol eliminado correctamente.');
    }
}
