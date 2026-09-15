<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UsuariosController extends Controller
{
    public function index(): View
    {
        $usuarios = User::with(['rol', 'sucursal'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('usuarios.index', compact('usuarios'));
    }

    public function create(): View
    {
        $roles = Rol::orderBy('tipo_rol')->get();
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();

        return view('usuarios.create', compact('roles', 'sucursales'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'nombre_usuario' => ['required', 'string', 'max:80', 'unique:usuarios,nombre_usuario'],
            'email' => ['required', 'email', 'unique:usuarios,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'id_rol' => ['required', 'exists:roles,id'],
            'id_sucursal' => ['nullable', 'exists:sucursales,id'],
            'es_activo' => ['boolean'],
        ]);

        $usuario = User::create([
            ...$data,
            'password' => bcrypt($data['password']),
            'es_activo' => $request->boolean('es_activo', true),
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario registrado correctamente.');
    }
}
