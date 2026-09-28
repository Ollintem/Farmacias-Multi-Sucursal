<?php

namespace Database\Seeders;

use App\Models\Modulo;
use Illuminate\Database\Seeder;

class ModuloSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (
            [
                'Dashboard',
                'Punto de venta',
                'Inventario',
                'Lotes y caducidades',
                'Entradas de almacén',
                'Traspasos',
                'Sucursales',
                'Usuarios y roles',
                'Caja',
                'Reportes',
                'Alertas',
            ] as $nombreModulo
        ) {
            Modulo::updateOrCreate(
                ['nombre_modulo' => $nombreModulo]
            );
        }
    }
}
