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
    public function index(Request $request): View
    {
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        $selectedSucursalId = session('active_sucursal_id') ?? $request->query('sucursal') ?? $sucursales->first()?->id;
        $selectedSucursal = $sucursales->firstWhere('id', $selectedSucursalId) ?? $sucursales->first();

        $query = User::with(['rol', 'sucursal']);

        if (auth()->user()->rol?->tipo_rol !== 'SuperAdmin') {
            $query->where('id_sucursal', $selectedSucursalId);
        } elseif ($selectedSucursal) {
            $query->where('id_sucursal', $selectedSucursalId);
        }

        $usuarios = $query->orderBy('created_at', 'desc')->get();

        $roles = Rol::withCount(['usuarios' => function ($query) use ($selectedSucursalId) {
            $query->where('id_sucursal', $selectedSucursalId);
        }])
            ->get()
            ->sortBy(function ($rol) {
                return $rol->tipo_rol === 'SuperAdmin' ? 0 : 1;
            })
            ->values();

        $totalUsuarios = User::where('id_sucursal', $selectedSucursalId)->count();

        return view('pages.usuarios.index', compact('usuarios', 'sucursales', 'selectedSucursal', 'roles', 'totalUsuarios'));
    }

    public function create(): View
    {
        $roles = Rol::where('tipo_rol', '!=', 'SuperAdmin')
            ->orderBy('tipo_rol')
            ->get();
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        $selectedSucursalId = session('active_sucursal_id') ?? auth()->user()?->id_sucursal;

        return view('pages.usuarios.create', compact('roles', 'sucursales', 'selectedSucursalId'));
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
            'password.required' => 'Escribe una contrasena para el usuario.',
            'password.min' => 'La contrasena debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contrasenas no coinciden. Escribelas nuevamente.',
            'password_confirmation.required' => 'Confirma la contrasena escribiendola nuevamente.',
        ]);

        if (empty($data['id_sucursal'])) {
            $data['id_sucursal'] = session('active_sucursal_id') ?? auth()->user()?->id_sucursal;
        }

        User::create([
            ...$data,
            'password' => bcrypt($data['password']),
            'es_activo' => $request->boolean('es_activo', true),
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario registrado correctamente.');
    }

    public function edit(User $usuario): View
    {
        $datos = $this->datosPermisos($usuario);

        return view('pages.usuarios.edit', [
            ...$datos,
            'usuario' => $usuario,
        ]);
    }

    public function permisos(User $usuario): View
    {
        $usuario->load(['rol', 'sucursal']);
        $datos = $this->datosPermisos($usuario);

        return view('pages.usuarios.permisos', [
            ...$datos,
            'usuario' => $usuario,
        ]);
    }

    public function updatePermisos(Request $request, User $usuario): RedirectResponse
    {
        $modulos = Modulo::orderBy('id')->get();
        $permisosInput = $request->input('permisos', []);

        $permisosIds = Permiso::whereIn('tipo_permiso', ['Mostrar', 'Crear', 'Editar', 'Borrar'])
            ->pluck('id', 'tipo_permiso');

        foreach ($modulos as $modulo) {
            $flagsModulo = is_array($permisosInput[$modulo->id] ?? null)
                ? $permisosInput[$modulo->id]
                : [];

            PermisoActivado::where('id_usuario', $usuario->id)
                ->where('id_modulo', $modulo->id)
                ->delete();

            if (! empty($flagsModulo['ver'])) {
                PermisoActivado::create([
                    'id_usuario' => $usuario->id,
                    'id_modulo' => $modulo->id,
                    'id_permiso' => $permisosIds['Mostrar'],
                    'es_activo' => true,
                ]);
            }
            if (! empty($flagsModulo['crear'])) {
                PermisoActivado::create([
                    'id_usuario' => $usuario->id,
                    'id_modulo' => $modulo->id,
                    'id_permiso' => $permisosIds['Crear'],
                    'es_activo' => true,
                ]);
            }
            if (! empty($flagsModulo['editar'])) {
                PermisoActivado::create([
                    'id_usuario' => $usuario->id,
                    'id_modulo' => $modulo->id,
                    'id_permiso' => $permisosIds['Editar'],
                    'es_activo' => true,
                ]);
            }
            if (! empty($flagsModulo['borrar'])) {
                PermisoActivado::create([
                    'id_usuario' => $usuario->id,
                    'id_modulo' => $modulo->id,
                    'id_permiso' => $permisosIds['Borrar'],
                    'es_activo' => true,
                ]);
            }
        }

        return redirect()->route('usuarios.permisos', $usuario)->with('success', 'Permisos actualizados correctamente.');
    }

    private function datosPermisos(User $usuario): array
    {
        $roles = Rol::where('tipo_rol', '!=', 'SuperAdmin')
            ->orderBy('tipo_rol')
            ->get();
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        $modulos = Modulo::orderBy('id')->get();

        $filas = PermisoActivado::where('id_usuario', $usuario->id)
            ->where('es_activo', true)
            ->with('permiso')
            ->get()
            ->groupBy('id_modulo');

        $permisosActivos = [];
        $permisosAgrupados = [];

        foreach ($modulos as $modulo) {
            $permisosModulo = $filas->get($modulo->id, collect());

            $tieneVer = $permisosModulo->contains(fn ($p) => $p->permiso?->tipo_permiso === 'Mostrar');
            $tieneCrear = $permisosModulo->contains(fn ($p) => $p->permiso?->tipo_permiso === 'Crear');
            $tieneEditar = $permisosModulo->contains(fn ($p) => $p->permiso?->tipo_permiso === 'Editar');
            $tieneBorrar = $permisosModulo->contains(fn ($p) => $p->permiso?->tipo_permiso === 'Borrar');
            $tieneTodos = $permisosModulo->contains(fn ($p) => $p->permiso?->tipo_permiso === 'Todos');

            $permisosActivos[$modulo->id] = [
                'ver' => $tieneVer || $tieneTodos,
                'crear' => $tieneCrear || $tieneTodos,
                'editar' => $tieneEditar || $tieneTodos,
                'borrar' => $tieneBorrar || $tieneTodos,
            ];

            $activos = [];
            if ($tieneVer || $tieneTodos) {
                $activos[] = 'Ver';
            }
            if ($tieneCrear || $tieneTodos) {
                $activos[] = 'Crear';
            }
            if ($tieneEditar || $tieneTodos) {
                $activos[] = 'Editar';
            }
            if ($tieneBorrar || $tieneTodos) {
                $activos[] = 'Borrar';
            }

            if ($activos !== []) {
                $permisosAgrupados[$modulo->nombre_modulo] = $activos;
            }
        }

        return compact('roles', 'sucursales', 'modulos', 'permisosActivos', 'permisosAgrupados');
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
            'permisos.*.ver' => ['nullable', 'boolean'],
            'permisos.*.crear' => ['nullable', 'boolean'],
            'permisos.*.editar' => ['nullable', 'boolean'],
            'permisos.*.borrar' => ['nullable', 'boolean'],
        ], [
            'password.min' => 'La contrasena debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contrasenas no coinciden. Escribelas nuevamente.',
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
        $permisosInput = $request->input('permisos', []);

        $permisosIds = Permiso::whereIn('tipo_permiso', ['Mostrar', 'Crear', 'Editar', 'Borrar'])
            ->pluck('id', 'tipo_permiso');

        foreach ($modulos as $modulo) {
            $flagsModulo = is_array($permisosInput[$modulo->id] ?? null)
                ? $permisosInput[$modulo->id]
                : [];

            PermisoActivado::where('id_usuario', $usuario->id)
                ->where('id_modulo', $modulo->id)
                ->delete();

            if (! empty($flagsModulo['ver'])) {
                PermisoActivado::create([
                    'id_usuario' => $usuario->id,
                    'id_modulo' => $modulo->id,
                    'id_permiso' => $permisosIds['Mostrar'],
                    'es_activo' => true,
                ]);
            }
            if (! empty($flagsModulo['crear'])) {
                PermisoActivado::create([
                    'id_usuario' => $usuario->id,
                    'id_modulo' => $modulo->id,
                    'id_permiso' => $permisosIds['Crear'],
                    'es_activo' => true,
                ]);
            }
            if (! empty($flagsModulo['editar'])) {
                PermisoActivado::create([
                    'id_usuario' => $usuario->id,
                    'id_modulo' => $modulo->id,
                    'id_permiso' => $permisosIds['Editar'],
                    'es_activo' => true,
                ]);
            }
            if (! empty($flagsModulo['borrar'])) {
                PermisoActivado::create([
                    'id_usuario' => $usuario->id,
                    'id_modulo' => $modulo->id,
                    'id_permiso' => $permisosIds['Borrar'],
                    'es_activo' => true,
                ]);
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
