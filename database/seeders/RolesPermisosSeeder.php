<?php

namespace Database\Seeders;

use App\Models\Modulo;
use App\Models\Rol;
use App\Models\RolPermiso;
use Illuminate\Database\Seeder;

class RolesPermisosSeeder extends Seeder
{
    /**
     * Acciones administradas por módulo.
     *
     * @var array<int, string>
     */
    private const ACCIONES = ['ver', 'crear', 'editar', 'borrar'];

    /**
     * Permisos operativos por rol.
     *
     * Los módulos que no aparecen para un rol se registran con `activo` en
     * false. SuperAdmin es la excepción: recibe todas las acciones activas.
     *
     * @return array<string, array<string, array<int, string>>>
     */
    private function permisosPorRol(): array
    {
        return [
            'Gerente' => [
                'Dashboard' => ['ver'],
                'Punto de venta' => ['ver', 'crear'],
                'Inventario' => ['ver', 'crear', 'editar'],
                'Lotes y caducidades' => ['ver', 'crear', 'editar'],
                'Entradas de almacén' => ['ver', 'crear', 'editar'],
                'Proveedores' => ['ver', 'crear', 'editar'],
                'Traspasos' => ['ver', 'crear', 'editar'],
                'Caja' => ['ver', 'crear', 'editar'],
                'Reportes' => ['ver'],
                'Alertas' => ['ver', 'editar'],
            ],
            'Cajero' => [
                'Punto de venta' => ['ver', 'crear'],
                'Inventario' => ['ver'],
                'Caja' => ['ver', 'crear'],
            ],
        ];
    }

    /**
     * Registra los permisos iniciales de SuperAdmin, Gerente y Cajero.
     *
     * Usa insertOrIgnore para que las filas existentes conserven el valor que
     * un administrador haya guardado, mientras los permisos nuevos o los
     * módulos incorporados después se agregan automáticamente.
     */
    public function run(): void
    {
        $this->call(PermisoSeeder::class);

        $configuracion = $this->permisosPorRol();
        $roles = Rol::query()
            ->whereIn('tipo_rol', ['SuperAdmin', 'Gerente', 'Cajero'])
            ->get()
            ->keyBy('tipo_rol');

        $modulos = Modulo::query()
            ->with(['permisos' => fn ($query) => $query->orderBy('id')])
            ->orderBy('id')
            ->get();

        $ahora = now();
        $filas = [];

        foreach ($roles as $tipoRol => $rol) {
            $porModulo = $configuracion[$tipoRol] ?? [];
            $esSuperAdmin = $tipoRol === 'SuperAdmin';

            foreach ($modulos as $modulo) {
                $accionesActivas = $esSuperAdmin
                    ? self::ACCIONES
                    : ($porModulo[$modulo->nombre_modulo] ?? []);

                foreach ($modulo->permisos as $permiso) {
                    $filas[] = [
                        'id_rol' => $rol->id,
                        'id_permiso' => $permiso->id,
                        'activo' => in_array($permiso->accion, $accionesActivas, true),
                        'created_at' => $ahora,
                        'updated_at' => $ahora,
                    ];
                }
            }
        }

        foreach (array_chunk($filas, 500) as $chunk) {
            RolPermiso::insertOrIgnore($chunk);
        }
    }
}
