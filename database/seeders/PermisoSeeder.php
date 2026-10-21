<?php

namespace Database\Seeders;

use App\Models\Modulo;
use App\Models\Permiso;
use Illuminate\Database\Seeder;

class PermisoSeeder extends Seeder
{
    /**
     * Acciones que se administran para cada módulo.
     *
     * @var array<int, string>
     */
    private const ACCIONES = ['ver', 'crear', 'editar', 'borrar'];

    /**
     * Sincroniza el catálogo de permisos granulares a partir de los módulos.
     */
    public function run(): void
    {
        foreach (Modulo::query()->orderBy('id')->get() as $modulo) {
            foreach (self::ACCIONES as $accion) {
                Permiso::firstOrCreate([
                    'id_modulo' => $modulo->id,
                    'accion' => $accion,
                ]);
            }
        }
    }
}
