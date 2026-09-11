<?php

namespace Database\Seeders;

use App\Models\Sucursal;
use Illuminate\Database\Seeder;

class SucursalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Sucursal::updateOrCreate(
            ['nombre_sucursal' => 'Sucursal Centro'],
            [
                'direccion' => 'Av. Principal 123, Centro',
                'hora_apertura' => '08:00:00',
                'hora_cierre' => '20:00:00',
            ]
        );

        Sucursal::updateOrCreate(
            ['nombre_sucursal' => 'Sucursal Norte'],
            [
                'direccion' => 'Calle Norte 456, Zona Norte',
                'hora_apertura' => '09:00:00',
                'hora_cierre' => '21:00:00',
            ]
        );
    }
}
