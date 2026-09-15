<?php

namespace Database\Seeders;

use App\Models\Modulo;
use App\Models\Permiso;
use App\Models\PermisoActivado;
use App\Models\User;
use Illuminate\Database\Seeder;

class PermisoActivadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->first()
            ?? User::find(1);

        $pruebas = User::where('email', 'test@example.com')->first()
            ?? User::find(2);

        if (! $admin || ! $pruebas) {
            return;
        }

        $modulos = Modulo::all();
        $permisos = Permiso::all()->keyBy('tipo_permiso');

        // Usuario 1 (admin): todos los permisos en todos los módulos.
        foreach ($modulos as $modulo) {
            foreach ($permisos as $permiso) {
                PermisoActivado::updateOrCreate(
                    [
                        'id_usuario' => $admin->id,
                        'id_modulo' => $modulo->id,
                        'id_permiso' => $permiso->id,
                    ],
                    ['es_activo' => true]
                );
            }
        }

        // Usuario 2 (pruebas): solo un subconjunto operativo básico.
        $accesosLimitados = [
            'Dashboard' => ['Mostrar'],
            'Punto de venta' => ['Crear', 'Mostrar'],
            'Inventario' => ['Mostrar'],
            'Caja' => ['Crear', 'Mostrar'],
            'Alertas' => ['Mostrar'],
        ];

        foreach ($accesosLimitados as $nombreModulo => $tiposPermiso) {
            $modulo = $modulos->firstWhere('nombre_modulo', $nombreModulo);

            if (! $modulo) {
                continue;
            }

            foreach ($tiposPermiso as $tipoPermiso) {
                $permiso = $permisos->get($tipoPermiso);

                if (! $permiso) {
                    continue;
                }

                PermisoActivado::updateOrCreate(
                    [
                        'id_usuario' => $pruebas->id,
                        'id_modulo' => $modulo->id,
                        'id_permiso' => $permiso->id,
                    ],
                    ['es_activo' => true]
                );
            }
        }
    }
}
