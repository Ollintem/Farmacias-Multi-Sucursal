<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Marca que el producto se vende tambien por unidad suelta.
     *
     * Antes la venta unitaria se inferia de `productos.precio IS NOT NULL`.
     * Se separa en columna propia para que un producto divisible conserve su
     * precio unitario sin exigir presentaciones, y para que uno unitario no
     * dependa de una columna condicional. Los productos existentes con precio
     * ya usaban esa inferencia, por lo que se marcan como `true`.
     */
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->boolean('vender_por_unidad')->default(false)->after('precio');
        });

        DB::table('productos')
            ->whereNotNull('precio')
            ->update(['vender_por_unidad' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn('vender_por_unidad');
        });
    }
};
