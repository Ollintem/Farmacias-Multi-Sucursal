<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (
            [
                'SuperAdmin' => 'Acceso total al sistema',
                'Gerente' => 'Gestión de sucursal y personal',
                'Cajero' => 'Ventas y cobros en caja',
            ] as $tipoRol => $descripcion
        ) {
            Rol::updateOrCreate(
                ['tipo_rol' => $tipoRol],
                ['descripcion' => $descripcion]
            );
        }
    }
}
