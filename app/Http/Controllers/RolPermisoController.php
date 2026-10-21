<?php

namespace App\Http\Controllers;

use App\Models\Modulo;
use App\Models\Permiso;
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
     * Entrada: query string opcional `rol`.
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
            ->with(['permisos' => fn ($query) => $query->orderBy('id')])
            ->orderBy('id')
            ->get();

        $configuracion = $selectedRol === null
            ? collect()
            : RolPermiso::query()
                ->where('id_rol', $selectedRol->id)
                ->get()
                ->keyBy('id_permiso');

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
            'puedeEditar' => $request->user()?->permisosHabilitados('editar', 'usuarios') ?? false,
        ]);
    }

    /**
     * Guarda la configuración de permisos de un rol sin tocar otros roles ni
     * las excepciones individuales de `permisos_activados`.
     *
     * Entrada: rol resuelto por la ruta y permisos marcados en el formulario.
     * Salida: redirección a la configuración del mismo rol.
     */
    public function update(Request $request, Rol $rol): RedirectResponse
    {
        if ($rol->tipo_rol === 'SuperAdmin') {
            return back()->with('error', 'El rol SuperAdmin siempre tiene acceso total y no se puede restringir.');
        }

        $data = $request->validate([
            'permisos' => ['nullable', 'array'],
            'permisos.*' => ['boolean'],
        ]);

        $activos = collect($data['permisos'] ?? [])
            ->filter(fn ($valor): bool => (bool) $valor)
            ->keys()
            ->map(fn ($id): int => (int) $id);

        $permisos = Permiso::query()->orderBy('id')->get();
        $idsValidos = $permisos->pluck('id');

        if ($activos->diff($idsValidos)->isNotEmpty()) {
            return back()
                ->withInput()
                ->withErrors(['permisos' => 'La configuración contiene permisos que no existen.']);
        }

        DB::transaction(function () use ($rol, $permisos, $activos): void {
            foreach ($permisos as $permiso) {
                RolPermiso::updateOrCreate(
                    [
                        'id_rol' => $rol->id,
                        'id_permiso' => $permiso->id,
                    ],
                    ['activo' => $activos->contains($permiso->id)]
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
}
