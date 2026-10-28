<?php

namespace App\Http\Controllers;

use App\Models\CorteCiego;
use App\Models\Modulo;
use App\Models\Permiso;
use App\Models\PermisoActivado;
use App\Models\Rol;
use App\Models\RolPermiso;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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

        $modulos = Modulo::orderBy('id')->get();
        $permisosPorRol = $this->mapaPermisosPorRol($roles);

        return view('pages.usuarios.create', compact('roles', 'sucursales', 'selectedSucursalId', 'modulos', 'permisosPorRol'));
    }

    /**
     * Mapa rol => modulo => flags desde roles_permisos (fuente BD, no hardcode).
     *
     * @param  Collection<int, Rol>  $roles
     * @return array<int, array<int, array{ver:bool, crear:bool, editar:bool, borrar:bool}>>
     */
    private function mapaPermisosPorRol($roles): array
    {
        $mapa = [];
        $permisos = Permiso::with('modulo')->get()->keyBy('id');

        $activos = RolPermiso::whereIn('id_rol', $roles->pluck('id'))
            ->where('activo', true)
            ->get()
            ->groupBy('id_rol');

        foreach ($roles as $rol) {
            $porModulo = [];
            foreach ($activos->get($rol->id, collect()) as $rp) {
                $permiso = $permisos->get($rp->id_permiso);
                if (! $permiso) {
                    continue;
                }
                $idModulo = $permiso->id_modulo;
                $accion = strtolower($permiso->accion);
                if (! isset($porModulo[$idModulo])) {
                    $porModulo[$idModulo] = ['ver' => false, 'crear' => false, 'editar' => false, 'borrar' => false];
                }
                if (array_key_exists($accion, $porModulo[$idModulo])) {
                    $porModulo[$idModulo][$accion] = true;
                }
            }
            $mapa[$rol->id] = $porModulo;
        }

        return $mapa;
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:usuarios,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string'],
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
            'password.required' => 'Escribe una contrasena para el usuario.',
            'password.min' => 'La contrasena debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contrasenas no coinciden. Escribelas nuevamente.',
            'password_confirmation.required' => 'Confirma la contrasena escribiendola nuevamente.',
        ]);

        if (empty($data['id_sucursal'])) {
            $data['id_sucursal'] = session('active_sucursal_id') ?? auth()->user()?->id_sucursal;
        }

        $usuario = null;
        DB::transaction(function () use ($request, $data, &$usuario) {
            $usuario = User::create([
                'nombre' => $data['nombre'],
                'apellido' => $data['apellido'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'id_rol' => $data['id_rol'],
                'id_sucursal' => $data['id_sucursal'],
                'es_activo' => $request->boolean('es_activo', true),
            ]);

            $permisosInput = $request->input('permisos', []);
            if (is_array($permisosInput) && $permisosInput !== []) {
                $modulos = Modulo::orderBy('id')->get();
                foreach ($modulos as $modulo) {
                    $flags = is_array($permisosInput[$modulo->id] ?? null) ? $permisosInput[$modulo->id] : [];
                    $fila = [
                        'puede_ver' => ! empty($flags['ver']),
                        'puede_crear' => ! empty($flags['crear']),
                        'puede_editar' => ! empty($flags['editar']),
                        'puede_borrar' => ! empty($flags['borrar']),
                    ];
                    if (in_array(true, $fila, true)) {
                        PermisoActivado::create([
                            'id_usuario' => $usuario->id,
                            'id_modulo' => $modulo->id,
                            ...$fila,
                        ]);
                    }
                }
            }
        });

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

        foreach ($modulos as $modulo) {
            $flagsModulo = is_array($permisosInput[$modulo->id] ?? null)
                ? $permisosInput[$modulo->id]
                : [];

            $flags = [
                'puede_ver' => ! empty($flagsModulo['ver']),
                'puede_crear' => ! empty($flagsModulo['crear']),
                'puede_editar' => ! empty($flagsModulo['editar']),
                'puede_borrar' => ! empty($flagsModulo['borrar']),
            ];

            if (in_array(true, $flags, true)) {
                PermisoActivado::updateOrCreate(
                    [
                        'id_usuario' => $usuario->id,
                        'id_modulo' => $modulo->id,
                    ],
                    $flags
                );
            } else {
                PermisoActivado::where('id_usuario', $usuario->id)
                    ->where('id_modulo', $modulo->id)
                    ->delete();
            }
        }

        CorteCiego::updateOrCreate(
            ['id_usuarios' => $usuario->id],
            ['corte_ciego' => $request->boolean('corte_ciego')],
        );

        return redirect()->route('usuarios.permisos', $usuario)->with('success', 'Permisos actualizados correctamente.');
    }

    /**
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

        $permisosActivos = [];
        $permisosAgrupados = [];

        foreach ($modulos as $modulo) {
            /** @var PermisoActivado|null $fila */
            $fila = $filas->get($modulo->id);

            $tieneVer = $fila !== null && $fila->puede_ver;
            $tieneCrear = $fila !== null && $fila->puede_crear;
            $tieneEditar = $fila !== null && $fila->puede_editar;
            $tieneBorrar = $fila !== null && $fila->puede_borrar;

            $permisosActivos[$modulo->id] = [
                'ver' => $tieneVer,
                'crear' => $tieneCrear,
                'editar' => $tieneEditar,
                'borrar' => $tieneBorrar,
            ];

            $activos = [];
            if ($tieneVer) {
                $activos[] = 'Ver';
            }
            if ($tieneCrear) {
                $activos[] = 'Crear';
            }
            if ($tieneEditar) {
                $activos[] = 'Editar';
            }
            if ($tieneBorrar) {
                $activos[] = 'Borrar';
            }

            if ($activos !== []) {
                $permisosAgrupados[$modulo->nombre_modulo] = $activos;
            }
        }

        $corteCiego = CorteCiego::where('id_usuarios', $usuario->id)->first();
        $corteCiegoActivo = $corteCiego?->corte_ciego ?? (bool) $usuario->rol?->corte_ciego;

        return compact('roles', 'sucursales', 'modulos', 'permisosActivos', 'permisosAgrupados', 'corteCiegoActivo');
    }

    public function update(Request $request, User $usuario): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
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

            $flags = [
                'puede_ver' => ! empty($flagsModulo['ver']),
                'puede_crear' => ! empty($flagsModulo['crear']),
                'puede_editar' => ! empty($flagsModulo['editar']),
                'puede_borrar' => ! empty($flagsModulo['borrar']),
            ];

            if (in_array(true, $flags, true)) {
                PermisoActivado::updateOrCreate(
                    [
                        'id_usuario' => $usuario->id,
                        'id_modulo' => $modulo->id,
                    ],
                    $flags
                );
            } else {
                PermisoActivado::where('id_usuario', $usuario->id)
                    ->where('id_modulo', $modulo->id)
                    ->delete();
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
