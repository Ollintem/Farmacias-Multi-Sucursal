<?php

namespace App\Http\Controllers;

use App\Models\Modulo;
use App\Models\PermisoActivado;
use App\Models\Rol;
use App\Models\RolPermiso;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RolPermisoController extends Controller
{
    /**
     * Muestra los roles disponibles y la configuración del rol seleccionado.
     *
     * Entrada: query string opcional `rol` y `usuario`.
     * Salida: resources/views/pages/roles/permisos.blade.php.
     */
    public function index(Request $request): View
    {
        $roles = Rol::query()
            ->withCount('usuarios')
            ->orderByRaw("CASE WHEN tipo_rol = 'SuperAdmin' THEN 0 ELSE 1 END")
            ->orderBy('tipo_rol')
            ->get();

        $selectedRolId = (int) ($request->query('rol') ?? $roles->first()?->id ?? 0);
        $selectedRol = $roles->firstWhere('id', $selectedRolId) ?? $roles->first();

        $modulos = Modulo::query()
            ->orderBy('id')
            ->get();

        $configuracion = $selectedRol === null
            ? collect()
            : RolPermiso::query()
                ->where('id_rol', $selectedRol->id)
                ->get()
                ->keyBy('id_modulo');

        $usuarios = $selectedRol === null
            ? collect()
            : User::query()
                ->where('id_rol', $selectedRol->id)
                ->orderBy('nombre')
                ->orderBy('apellido')
                ->get();

        $selectedUsuario = $usuarios->firstWhere('id', (int) $request->query('usuario', 0));

        $excepciones = $selectedUsuario === null
            ? collect()
            : PermisoActivado::query()
                ->where('id_usuario', $selectedUsuario->id)
                ->get()
                ->keyBy('id_modulo');

        return view('pages.roles.permisos', [
            'roles' => $roles,
            'selectedRol' => $selectedRol,
            'modulos' => $modulos,
            'configuracion' => $configuracion,
            'usuarios' => $usuarios,
            'selectedUsuario' => $selectedUsuario,
            'excepciones' => $excepciones,
            'puedeEditar' => $this->puedeAdministrar($request),
        ]);
    }

    /**
     * Guarda la configuración de permisos predeterminados de un rol.
     *
     * Entrada: rol resuelto por la ruta y flags por módulo del formulario.
     * Salida: redirección a la configuración del mismo rol.
     */
    public function update(Request $request, Rol $rol): RedirectResponse
    {
        abort_unless($this->puedeAdministrar($request), 403);

        if ($rol->tipo_rol === 'SuperAdmin') {
            return back()->with('error', 'El rol SuperAdmin siempre tiene acceso total y no se puede restringir.');
        }

        $data = $request->validate([
            'permisos' => ['nullable', 'array'],
            'permisos.*' => ['array'],
            'permisos.*.ver' => ['nullable', 'boolean'],
            'permisos.*.crear' => ['nullable', 'boolean'],
            'permisos.*.editar' => ['nullable', 'boolean'],
            'permisos.*.borrar' => ['nullable', 'boolean'],
            'corte_ciego' => ['nullable', 'boolean'],
        ]);

        $modulos = Modulo::query()->orderBy('id')->get();
        $idsValidos = $modulos->pluck('id');
        $idsEnviados = collect(array_keys($data['permisos'] ?? []))
            ->map(fn ($id): int => (int) $id);

        if ($idsEnviados->diff($idsValidos)->isNotEmpty()) {
            return back()
                ->withInput()
                ->withErrors(['permisos' => 'La configuración contiene módulos que no existen.']);
        }

        DB::transaction(function () use ($request, $rol, $modulos, $data): void {
            $rol->update([
                'corte_ciego' => $request->boolean('corte_ciego'),
            ]);

            foreach ($modulos as $modulo) {
                $flagsModulo = $data['permisos'][$modulo->id] ?? [];

                RolPermiso::updateOrCreate(
                    [
                        'id_rol' => $rol->id,
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
        });

        return redirect()
            ->route('roles.permisos', array_filter([
                'rol' => $rol->id,
                'usuario' => $request->input('usuario'),
            ]))
            ->with('success', 'Permisos del rol actualizados correctamente.');
    }

    /**
     * Indica si el usuario autenticado puede administrar permisos.
     */
    private function puedeAdministrar(Request $request): bool
    {
        $usuario = $request->user();

        return $usuario !== null
            && ($usuario->esSuperAdmin() || $usuario->permisosHabilitados('editar', 'usuarios'));
    }
}
