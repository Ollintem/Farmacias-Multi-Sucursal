<?php

namespace App\Http\Controllers;

use App\Models\CorteCiego;
use App\Models\Modulo;
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

        $permisos = RolPermiso::whereIn('id_rol', $roles->pluck('id'))
            ->get()
            ->groupBy('id_rol');

        foreach ($roles as $rol) {
            $porModulo = [];
            foreach ($permisos->get($rol->id, collect()) as $rp) {
                $porModulo[$rp->id_modulo] = [
                    'ver' => (bool) $rp->puede_ver,
                    'crear' => (bool) $rp->puede_crear,
                    'editar' => (bool) $rp->puede_editar,
                    'borrar' => (bool) $rp->puede_borrar,
                ];
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
            if (is_array($permisosInput)) {
                $this->sincronizarPermisosUsuario($usuario, $permisosInput);
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
        $data = $request->validate([
            'permisos' => ['nullable', 'array'],
            'permisos.*' => ['array'],
            'permisos.*.ver' => ['nullable', 'boolean'],
            'permisos.*.crear' => ['nullable', 'boolean'],
            'permisos.*.editar' => ['nullable', 'boolean'],
            'permisos.*.borrar' => ['nullable', 'boolean'],
            'permisos.*.heredar' => ['nullable', 'boolean'],
            'corte_ciego' => ['nullable', 'boolean'],
        ]);

        $this->sincronizarPermisosUsuario($usuario, $data['permisos'] ?? []);

        CorteCiego::updateOrCreate(
            ['id_usuarios' => $usuario->id],
            ['corte_ciego' => $request->boolean('corte_ciego')],
        );

        return redirect()->route('usuarios.permisos', $usuario)->with('success', 'Permisos actualizados correctamente.');
    }

    /**
     * Sincroniza los permisos personalizados del usuario.
     *
     * Si el módulo trae `heredar`, se elimina la configuración individual y el
     * usuario vuelve a heredar los permisos de su rol. En caso contrario se
     * guarda una fila propia, incluso si todos los flags son falsos, para que
     * el override individual tenga prioridad sobre el rol.
     *
     * @param  array<int|string, mixed>  $permisosInput
     */
    private function sincronizarPermisosUsuario(User $usuario, array $permisosInput): void
    {
        $modulos = Modulo::orderBy('id')->get();

        foreach ($modulos as $modulo) {
            if (! array_key_exists($modulo->id, $permisosInput)) {
                continue;
            }

            $flagsModulo = is_array($permisosInput[$modulo->id]) ? $permisosInput[$modulo->id] : [];

            if (! empty($flagsModulo['heredar'])) {
                PermisoActivado::where('id_usuario', $usuario->id)
                    ->where('id_modulo', $modulo->id)
                    ->delete();

                continue;
            }

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
                ]
            );
        }
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

        $permisosRol = $usuario->id_rol === null
            ? collect()
            : RolPermiso::where('id_rol', $usuario->id_rol)
                ->get()
                ->keyBy('id_modulo');

        $rolTieneConfiguracion = $permisosRol->isNotEmpty();
        $sinConfiguracionAlguna = ! $rolTieneConfiguracion && $filas->isEmpty();

        $permisosActivos = [];
        $permisosAgrupados = [];
        $permisosPersonalizados = [];

        foreach ($modulos as $modulo) {
            /** @var PermisoActivado|null $personalizado */
            $personalizado = $filas->get($modulo->id);
            /** @var RolPermiso|null $permisoRol */
            $permisoRol = $permisosRol->get($modulo->id);

            $permisosPersonalizados[$modulo->id] = $personalizado !== null;

            if ($personalizado !== null) {
                $flags = [
                    'ver' => (bool) $personalizado->puede_ver,
                    'crear' => (bool) $personalizado->puede_crear,
                    'editar' => (bool) $personalizado->puede_editar,
                    'borrar' => (bool) $personalizado->puede_borrar,
                ];
            } elseif ($permisoRol !== null) {
                $flags = [
                    'ver' => (bool) $permisoRol->puede_ver,
                    'crear' => (bool) $permisoRol->puede_crear,
                    'editar' => (bool) $permisoRol->puede_editar,
                    'borrar' => (bool) $permisoRol->puede_borrar,
                ];
            } else {
                $heredado = $sinConfiguracionAlguna;
                $flags = [
                    'ver' => $heredado,
                    'crear' => $heredado,
                    'editar' => $heredado,
                    'borrar' => $heredado,
                ];
            }

            $permisosActivos[$modulo->id] = $flags;

            $activos = [];
            if ($flags['ver']) {
                $activos[] = 'Ver';
            }
            if ($flags['crear']) {
                $activos[] = 'Crear';
            }
            if ($flags['editar']) {
                $activos[] = 'Editar';
            }
            if ($flags['borrar']) {
                $activos[] = 'Borrar';
            }

            if ($activos !== []) {
                $permisosAgrupados[$modulo->nombre_modulo] = $activos;
            }
        }

        $corteCiego = CorteCiego::where('id_usuarios', $usuario->id)->first();
        $corteCiegoActivo = $corteCiego?->corte_ciego ?? (bool) $usuario->rol?->corte_ciego;

        return compact('roles', 'sucursales', 'modulos', 'permisosActivos', 'permisosAgrupados', 'permisosPersonalizados', 'corteCiegoActivo');
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

        $permisosInput = $request->input('permisos', []);
        if (is_array($permisosInput)) {
            $this->sincronizarPermisosUsuario($usuario, $permisosInput);
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
