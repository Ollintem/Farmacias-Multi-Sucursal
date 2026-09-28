<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Quita ampolleta y pastilla del catalogo de presentaciones.
     *
     * Solo se eliminan si ningun producto las referencia: presentaciones.id
     * tiene ON DELETE CASCADE desde productos y presentacion_producto, por lo
     * que borrar una presentacion en uso borraria sus productos.
     */
    public function up(): void
    {
        DB::table('presentaciones')
            ->whereIn('presentacion', ['ampolleta', 'pastilla'])
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('productos')
                    ->whereColumn('productos.id_presentacion', 'presentaciones.id');
            })
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('presentacion_producto')
                    ->whereColumn('presentacion_producto.id_presentacion', 'presentaciones.id');
            })
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ([
            ['presentacion' => 'ampolleta', 'descripcion' => 'Recipiente sellado de vidrio para uso inyectable.'],
            ['presentacion' => 'pastilla', 'descripcion' => 'Unidad individual de medicamento solido.'],
        ] as $presentacion) {
            $existe = DB::table('presentaciones')
                ->where('presentacion', $presentacion['presentacion'])
                ->exists();

            if (! $existe) {
                DB::table('presentaciones')->insert($presentacion);
            }
        }
    }
};
