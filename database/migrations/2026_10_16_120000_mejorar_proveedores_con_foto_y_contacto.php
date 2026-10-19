<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mejora aditiva de proveedores sin perder datos:
     * foto opcional, RFC y contacto, baja lógica y textos más amplios.
     */
    public function up(): void
    {
        Schema::table('proveedores', function (Blueprint $table) {
            $table->string('foto_path', 255)->nullable()->after('correo');
            $table->string('rfc', 13)->nullable()->after('foto_path');
            $table->string('nombre_contacto', 120)->nullable()->after('rfc');
            $table->boolean('es_activo')->default(true)->after('nombre_contacto');
        });

        // Textos más amplios (nombre 20 y correo 30 se quedaban cortos).
        // Solo MySQL: SQLite ignora la longitud del VARCHAR.
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `proveedores` MODIFY `nombre_proveedor` VARCHAR(120) NOT NULL');
            DB::statement('ALTER TABLE `proveedores` MODIFY `unidad_entrega` VARCHAR(60) NOT NULL');
            DB::statement('ALTER TABLE `proveedores` MODIFY `telefono` VARCHAR(30) NOT NULL');
            DB::statement('ALTER TABLE `proveedores` MODIFY `correo` VARCHAR(120) NOT NULL');
        }

        // Correo único solo si los datos actuales lo permiten.
        $duplicados = DB::table('proveedores')
            ->select('correo', DB::raw('COUNT(*) as total'))
            ->groupBy('correo')
            ->having('total', '>', 1)
            ->exists();

        if (! $duplicados) {
            Schema::table('proveedores', function (Blueprint $table) {
                $table->unique('correo', 'proveedores_correo_unique');
            });
        }
    }

    public function down(): void
    {
        try {
            Schema::table('proveedores', function (Blueprint $table) {
                $table->dropUnique('proveedores_correo_unique');
            });
        } catch (Throwable) {
            // El índice no existe (había duplicados al migrar): nada que revertir.
        }

        Schema::table('proveedores', function (Blueprint $table) {
            $table->dropColumn(['foto_path', 'rfc', 'nombre_contacto', 'es_activo']);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `proveedores` MODIFY `nombre_proveedor` VARCHAR(20) NOT NULL');
            DB::statement('ALTER TABLE `proveedores` MODIFY `unidad_entrega` VARCHAR(20) NOT NULL');
            DB::statement('ALTER TABLE `proveedores` MODIFY `telefono` VARCHAR(20) NOT NULL');
            DB::statement('ALTER TABLE `proveedores` MODIFY `correo` VARCHAR(30) NOT NULL');
        }
    }
};
