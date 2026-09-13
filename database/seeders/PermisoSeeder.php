<?php

namespace Database\Seeders;

use App\Models\Permiso;
use Illuminate\Database\Seeder;

class PermisoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (
            [
                'Crear' => 'Permite crear registros',
                'Editar' => 'Permite editar registros',
                'Borrar' => 'Permite borrar registros',
                'Mostrar' => 'Permite ver registros',
                'Todos' => 'Acceso total, todos los permisos',
            ] as $tipoPermiso => $descripcion
        ) {
            Permiso::updateOrCreate(
                ['tipo_permiso' => $tipoPermiso],
                ['descripcion' => $descripcion]
            );
        }
    }
}
