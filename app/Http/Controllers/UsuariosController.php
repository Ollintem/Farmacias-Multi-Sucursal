<?php

namespace App\Http\Controllers;

use App\Models\Modulo;
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
    /**
     * Lista usuarios con su rol y sucursal precargados.
     *
     * Salida: resources/views/pages/usuarios/index.blade.php.
     */
    public function index(): View
    {
        $usuarios = User::with(['rol', 'sucursal'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pages.usuarios.index', compact('usuarios'));
    }

    /**
     * Carga roles y sucursales para el formulario de alta.
     *
     * Salida: resources/views/pages/usuarios/create.blade.php.
     */
    public function create(): View
    {
        $roles = Rol::where('tipo_rol', '!=', 'SuperAdmin')
            ->orderBy('tipo_rol')
            ->get();
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();

        return view('pages.usuarios.create', compact('roles', 'sucursales'));
    }

    /**
     * Valida y registra un usuario, excluyendo el rol SuperAdmin.
     *
     * Entrada: datos del formulario de alta.
     * Salida: redirección a usuarios.index.
     */
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

    /**
     * Muestra un usuario en modo consulta con sus permisos actuales.
     *
     * Entrada: usuario resuelto mediante route model binding.
     * Salida: resources/views/pages/usuarios/edit.blade.php en modo ver.
     */
    public function show(User $usuario): View
    {
        $usuario->load(['rol', 'sucursal']);
        $datos = $this->datosPermisos($usuario);

        return view('pages.usuarios.edit', [
            ...$datos,
            'usuario' => $usuario,
            'modo' => 'ver',
        ]);
    }

    /**
     * Carga un usuario en modo edición junto con catálogos y permisos.
     *
     * Entrada: usuario resuelto mediante route model binding.
     * Salida: resources/views/pages/usuarios/edit.blade.php en modo editar.
     */
    public function edit(User $usuario): View
    {
        $datos = $this->datosPermisos($usuario);

        return view('pages.usuarios.edit', [
            ...$datos,
            'usuario' => $usuario,
            'modo' => 'editar',
        ]);
    }

    /**
     * Datos compartidos por show() y edit(): catálogos, matriz de
     * permisos y permisos activados del usuario (planos y agrupados).
     *
     * @return array<string, mixed>
     */
    private function datosPermisos(User $usuario): array
    {
        $roles = Rol::where('tipo_rol', '!=', 'SuperAdmin')
            ->orderBy('tipo_rol')
            ->get();
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        $modulos = Modulo::orderBy('id')->get();
        $filas = PermisoActivado::where('id_usuario', $usuario->id)
            ->get()
            ->keyBy('id_modulo');

        $etiquetas = [
            'puede_ver' => 'Ver',
            'puede_crear' => 'Crear',
            'puede_editar' => 'Editar',
            'puede_borrar' => 'Borrar',
        ];

        $permisosActivos = [];
        $permisosAgrupados = [];

        foreach ($modulos as $modulo) {
            $fila = $filas->get($modulo->id);

            $permisosActivos[$modulo->id] = [
                'ver' => (bool) ($fila?->puede_ver ?? false),
                'crear' => (bool) ($fila?->puede_crear ?? false),
                'editar' => (bool) ($fila?->puede_editar ?? false),
                'borrar' => (bool) ($fila?->puede_borrar ?? false),
            ];

            $activos = [];
            foreach ($etiquetas as $columna => $etiqueta) {
                if ($fila && (bool) $fila->{$columna}) {
                    $activos[] = $etiqueta;
                }
            }

            if ($activos !== []) {
                $permisosAgrupados[$modulo->nombre_modulo] = $activos;
            }
        }

        return compact('roles', 'sucursales', 'modulos', 'permisosActivos', 'permisosAgrupados');
    }

    /**
     * Actualiza datos del usuario y reconstruye su matriz de permisos.
     *
     * Entrada: datos del formulario y usuario resuelto por la ruta.
     * Salida: redirección a usuarios.edit.
     */
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
        $permisosInput = $request->input('permisos', []);

        foreach ($modulos as $modulo) {
            $flagsModulo = is_array($permisosInput[$modulo->id] ?? null)
                ? $permisosInput[$modulo->id]
                : [];

            PermisoActivado::updateOrCreate(
                [
                    'id_usuario' => $usuario->id,
                    'id_modulo' => $modulo->id,
                ],
                [
                    'puede_ver' => ! empty($flagsModulo['ver']),
                    'puede_crear' => ! empty($flagsModulo['crear']),
                    'puede_editar' => ! empty($flagsModulo['editar']),
                    'puede_borrar' => ! empty($flagsModulo['borrar']),
                ],
            );
        }

        return redirect()->route('usuarios.edit', $usuario)->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Elimina un usuario salvo que sea el usuario autenticado o un SuperAdmin.
     *
     * Entrada: usuario resuelto mediante route model binding.
     * Salida: redirección a usuarios.index con mensaje de resultado.
     */
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
