<?php

namespace Database\Seeders;

use App\Models\Modulo;
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

        // Usuario 1 (admin): acceso total en todos los módulos.
        foreach ($modulos as $modulo) {
            PermisoActivado::updateOrCreate(
                [
                    'id_usuario' => $admin->id,
                    'id_modulo' => $modulo->id,
                ],
                [
                    'puede_ver' => true,
                    'puede_crear' => true,
                    'puede_editar' => true,
                    'puede_borrar' => true,
                ]
            );
        }

        // Usuario 2 (pruebas): permisos limitados, solo operativo básico.
        $accesosLimitados = [
            'Dashboard' => ['puede_ver' => true, 'puede_crear' => false, 'puede_editar' => false, 'puede_borrar' => false],
            'Punto de venta' => ['puede_ver' => true, 'puede_crear' => true, 'puede_editar' => false, 'puede_borrar' => false],
            'Inventario' => ['puede_ver' => true, 'puede_crear' => false, 'puede_editar' => false, 'puede_borrar' => false],
            'Caja' => ['puede_ver' => true, 'puede_crear' => true, 'puede_editar' => false, 'puede_borrar' => false],
            'Alertas' => ['puede_ver' => true, 'puede_crear' => false, 'puede_editar' => false, 'puede_borrar' => false],
        ];

        foreach ($accesosLimitados as $nombreModulo => $flags) {
            $modulo = $modulos->firstWhere('nombre_modulo', $nombreModulo);

            if (! $modulo) {
                continue;
            }

            PermisoActivado::updateOrCreate(
                [
                    'id_usuario' => $pruebas->id,
                    'id_modulo' => $modulo->id,
                ],
                $flags
            );
        }
    }
}
