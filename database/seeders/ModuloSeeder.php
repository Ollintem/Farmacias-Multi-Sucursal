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
                'Entradas de almacén',
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
