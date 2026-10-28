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
     *
     * Se revisan las columnas existentes para que la migración funcione tanto
     * con instalaciones anteriores como con el esquema base actual, que puede
     * declarar `RFC` o `unidad_entrega` según la versión de la migración base.
     */
    public function up(): void
    {
        $originales = $this->columnas('proveedores');

        Schema::table('proveedores', function (Blueprint $table) use ($originales): void {
            if (! in_array('unidad_entrega', $originales, true)) {
                $table->string('unidad_entrega', 60)->nullable()->after('direccion');
            }

            if (! in_array('foto_path', $originales, true)) {
                $table->string('foto_path', 255)->nullable()->after('correo');
            }

            if (! in_array('rfc', $originales, true)) {
                $table->string('rfc', 13)->nullable()->after('foto_path');
            }

            if (! in_array('nombre_contacto', $originales, true)) {
                $table->string('nombre_contacto', 120)->nullable()->after('rfc');
            }

            if (! in_array('es_activo', $originales, true)) {
                $table->boolean('es_activo')->default(true)->after('nombre_contacto');
            }
        });

        // El RFC es opcional en la aplicación; se normaliza aunque el esquema
        // base lo haya declarado obligatorio, con otro tamaño o con mayúsculas.
        $columnaRfc = $this->columnaReal('proveedores', 'rfc');

        if ($columnaRfc !== null && $columnaRfc !== 'rfc') {
            Schema::table('proveedores', function (Blueprint $table) use ($columnaRfc): void {
                $table->renameColumn($columnaRfc, 'rfc');
            });

            $columnaRfc = 'rfc';
        }

        if ($columnaRfc !== null) {
            Schema::table('proveedores', function (Blueprint $table) use ($columnaRfc): void {
                $table->string($columnaRfc, 13)->nullable()->change();
            });
        }

        // Textos más amplios (nombre 20 y correo 30 se quedaban cortos).
        // Solo MySQL: SQLite ignora la longitud del VARCHAR.
        if (DB::getDriverName() === 'mysql') {
            $columnas = $this->columnas('proveedores');

            foreach ([
                'nombre_proveedor' => 'VARCHAR(120) NOT NULL',
                'unidad_entrega' => 'VARCHAR(60) NOT NULL',
                'telefono' => 'VARCHAR(30) NOT NULL',
                'correo' => 'VARCHAR(120) NOT NULL',
            ] as $columna => $definicion) {
                if (in_array($columna, $columnas, true)) {
                    DB::statement("ALTER TABLE `proveedores` MODIFY `{$columna}` {$definicion}");
                }
            }
        }

        // Correo único solo si los datos actuales lo permiten.
        $duplicados = DB::table('proveedores')
            ->select('correo', DB::raw('COUNT(*) as total'))
            ->groupBy('correo')
            ->having('total', '>', 1)
            ->exists();

        if (! $duplicados) {
            Schema::table('proveedores', function (Blueprint $table): void {
                $table->unique('correo', 'proveedores_correo_unique');
            });
        }
    }

    public function down(): void
    {
        try {
            Schema::table('proveedores', function (Blueprint $table): void {
                $table->dropUnique('proveedores_correo_unique');
            });
        } catch (Throwable) {
            // El índice no existe (había duplicados al migrar): nada que revertir.
        }

        $columnas = $this->columnas('proveedores');

        Schema::table('proveedores', function (Blueprint $table) use ($columnas): void {
            $aEliminar = array_values(array_filter(
                ['foto_path', 'rfc', 'nombre_contacto', 'es_activo'],
                fn (string $columna): bool => in_array($columna, $columnas, true)
            ));

            if ($aEliminar !== []) {
                $table->dropColumn($aEliminar);
            }
        });

        if (DB::getDriverName() === 'mysql') {
            $columnas = $this->columnas('proveedores');

            foreach ([
                'nombre_proveedor' => 'VARCHAR(20) NOT NULL',
                'unidad_entrega' => 'VARCHAR(20) NOT NULL',
                'telefono' => 'VARCHAR(20) NOT NULL',
                'correo' => 'VARCHAR(30) NOT NULL',
            ] as $columna => $definicion) {
                if (in_array($columna, $columnas, true)) {
                    DB::statement("ALTER TABLE `proveedores` MODIFY `{$columna}` {$definicion}");
                }
            }
        }
    }

    /**
     * @return array<int, string>
     */
    private function columnas(string $tabla): array
    {
        return array_map('strtolower', Schema::getColumnListing($tabla));
    }

    /**
     * Nombre real de una columna, sin depender de mayúsculas/minúsculas.
     */
    private function columnaReal(string $tabla, string $columna): ?string
    {
        foreach (Schema::getColumnListing($tabla) as $nombre) {
            if (strtolower($nombre) === strtolower($columna)) {
                return $nombre;
            }
        }

        return null;
    }
};
