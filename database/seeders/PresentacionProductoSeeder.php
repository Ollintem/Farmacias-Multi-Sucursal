<?php

namespace Database\Seeders;

use App\Models\PresentacionProducto;
use Illuminate\Database\Seeder;

class PresentacionProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (
            [
                ['presentacion' => 'caja', 'descripcion' => 'Empaque con múltiples unidades del producto.'],
                ['presentacion' => 'blister', 'descripcion' => 'Lámina con dosis individuales selladas.'],
                ['presentacion' => 'ampolleta', 'descripcion' => 'Recipiente sellado de vidrio para uso inyectable.'],
                ['presentacion' => 'pastilla', 'descripcion' => 'Unidad individual de medicamento sólido.'],
            ] as $presentacion
        ) {
            PresentacionProducto::updateOrCreate(
                ['presentacion' => $presentacion['presentacion']],
                ['descripcion' => $presentacion['descripcion']]
            );
        }
    }
}
