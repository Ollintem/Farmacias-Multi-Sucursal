<?php

namespace App\Http\Controllers;

use App\Models\Modulo;
use App\Models\Permiso;
use App\Models\PermisoActivado;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UsuariosController extends Controller
{
    public function index(): View
    {
        $usuarios = User::with(['rol', 'sucursal'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pages.usuarios.index', compact('usuarios'));
    }

    public function create(): View
    {
        $roles = Rol::where('tipo_rol', '!=', 'SuperAdmin')
            ->orderBy('tipo_rol')
            ->get();
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();

        return view('pages.usuarios.create', compact('roles', 'sucursales'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'nombre_usuario' => ['required', 'string', 'max:80', 'unique:usuarios,nombre_usuario'],
            'email' => ['required', 'email', 'unique:usuarios,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'id_rol' => [
                'required',
                Rule::exists('roles', 'id')->where(fn ($query) => $query->where('tipo_rol', '!=', 'SuperAdmin')),
            ],
            'id_sucursal' => ['nullable', 'exists:sucursales,id'],
            'es_activo' => ['boolean'],
        ], [
            'password.required' => 'Escribe una contraseña para el usuario.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden. Escríbelas nuevamente.',
            'password_confirmation.required' => 'Confirma la contraseña escribiéndola nuevamente.',
        ]);

        $usuario = User::create([
            ...$data,
            'password' => bcrypt($data['password']),
            'es_activo' => $request->boolean('es_activo', true),
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario registrado correctamente.');
    }

    public function edit(User $usuario): View
    {
        $roles = Rol::where('tipo_rol', '!=', 'SuperAdmin')
            ->orderBy('tipo_rol')
            ->get();
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        $modulos = Modulo::orderBy('id')->get();
        $permisos = Permiso::orderBy('id')->get();
        $permisosActivos = PermisoActivado::where('id_usuario', $usuario->id)
            ->where('es_activo', true)
            ->get()
            ->mapWithKeys(fn (PermisoActivado $permisoActivado) => [
                $permisoActivado->id_modulo.'-'.$permisoActivado->id_permiso => true,
            ])
            ->all();

        return view('pages.usuarios.edit', compact('usuario', 'roles', 'sucursales', 'modulos', 'permisos', 'permisosActivos'));
    }

    public function update(Request $request, User $usuario): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'nombre_usuario' => ['required', 'string', 'max:80', Rule::unique('usuarios', 'nombre_usuario')->ignore($usuario->id)],
            'email' => ['required', 'email', Rule::unique('usuarios', 'email')->ignore($usuario->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'id_rol' => [
                'required',
                Rule::exists('roles', 'id')->where(fn ($query) => $query->where('tipo_rol', '!=', 'SuperAdmin')),
            ],
            'id_sucursal' => ['nullable', 'exists:sucursales,id'],
            'es_activo' => ['boolean'],
            'permisos' => ['nullable', 'array'],
            'permisos.*' => ['array'],
            'permisos.*.*' => ['integer', Rule::exists('permisos', 'id')],
        ], [
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden. Escríbelas nuevamente.',
        ]);

        $usuario->update([
            'nombre' => $data['nombre'],
            'apellido' => $data['apellido'],
            'nombre_usuario' => $data['nombre_usuario'],
            'email' => $data['email'],
            'id_rol' => $data['id_rol'],
            'id_sucursal' => $data['id_sucursal'] ?? null,
            'es_activo' => $request->boolean('es_activo'),
            ...($request->filled('password') ? ['password' => bcrypt($data['password'])] : []),
        ]);

        $modulos = Modulo::orderBy('id')->get();
        $permisos = Permiso::orderBy('id')->get();
        $seleccionados = collect($request->input('permisos', []))
            ->mapWithKeys(fn ($permisoIds, $moduloId) => [
                (int) $moduloId => collect($permisoIds)->map(fn ($permisoId) => (int) $permisoId)->all(),
            ])
            ->all();

        foreach ($modulos as $modulo) {
            foreach ($permisos as $permiso) {
                PermisoActivado::updateOrCreate(
                    [
                        'id_usuario' => $usuario->id,
                        'id_modulo' => $modulo->id,
                        'id_permiso' => $permiso->id,
                    ],
                    ['es_activo' => in_array($permiso->id, $seleccionados[$modulo->id] ?? [], true)],
                );
            }
        }

        return redirect()->route('usuarios.edit', $usuario)->with('success', 'Usuario actualizado correctamente.');
    }

    public function delete(User $usuario): RedirectResponse
    {
        if ($usuario->is(auth()->user())) {
            return redirect()->route('usuarios.index')->with('error', 'No puedes eliminar tu propio usuario.');
        }

        if ($usuario->rol?->tipo_rol === 'SuperAdmin') {
            return redirect()->route('usuarios.index')->with('error', 'No se puede eliminar a un SuperAdmin.');
        }

        $usuario->delete();

        return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado correctamente.');
    }
}
