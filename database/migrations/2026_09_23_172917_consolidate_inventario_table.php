<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Consolida las tablas legacy en `inventario` para BDs ya migradas.
     * En migrate:fresh es no-op porque la migración base ya crea `inventario`.
     */
    public function up(): void
    {
        if (! Schema::hasTable('inventario')) {
            Schema::create('inventario', function (Blueprint $table) {
                $table->id();
                $table->foreignId('id_sucursal')->constrained('sucursales')->cascadeOnDelete();
                $table->foreignId('id_producto')->constrained('productos')->cascadeOnDelete();
                $table->integer('stock')->default(0);
                $table->timestamps();

                $table->unique(['id_sucursal', 'id_producto']);
            });
        }

        if (Schema::hasTable('producto_sucursal')) {
            $filas = DB::table('producto_sucursal')->get(['producto', 'sucursal']);

            foreach ($filas as $fila) {
                DB::table('inventario')->updateOrInsert(
                    ['id_sucursal' => $fila->sucursal, 'id_producto' => $fila->producto],
                    ['stock' => 0]
                );
            }

            Schema::dropIfExists('producto_sucursal');
        }

        if (Schema::hasTable('inventarios')) {
            $filas = DB::table('inventarios')->get(['id_sucursal', 'id_producto', 'stock']);

            foreach ($filas as $fila) {
                DB::table('inventario')->updateOrInsert(
                    ['id_sucursal' => $fila->id_sucursal, 'id_producto' => $fila->id_producto],
                    ['stock' => $fila->stock ?? 0]
                );
            }

            Schema::dropIfExists('inventarios');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventario');
    }
};
