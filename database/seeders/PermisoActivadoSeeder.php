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
        $superAdmins = User::whereHas('rol', fn ($query) => $query->where('tipo_rol', 'SuperAdmin'))->get();

        if ($superAdmins->isEmpty()) {
            return;
        }

        $modulos = Modulo::all();

        foreach ($superAdmins as $superAdmin) {
            foreach ($modulos as $modulo) {
                PermisoActivado::updateOrCreate(
                    [
                        'id_usuario' => $superAdmin->id,
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
        }
    }
}
