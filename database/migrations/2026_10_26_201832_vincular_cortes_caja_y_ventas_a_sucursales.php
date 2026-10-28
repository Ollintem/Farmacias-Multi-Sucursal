<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sustituye la relación con `cajas` por la sucursal directa en los cortes
     * de caja y en las ventas, conservando el historial existente.
     *
     * Las migraciones base siguen creando `cajas` para poder actualizar bases
     * ya instaladas; en una instalación nueva esta migración la elimina al
     * final y deja `cortes_caja` y `ventas` ligadas a `sucursales`.
     */
    public function up(): void
    {
        $cajasExiste = Schema::hasTable('cajas');
        $cortesUsaCaja = Schema::hasColumn('cortes_caja', 'id_caja');
        $ventasUsaCaja = Schema::hasColumn('ventas', 'id_caja');

        if (! $cajasExiste && ! $cortesUsaCaja && ! $ventasUsaCaja) {
            return;
        }

        if ($cortesUsaCaja) {
            $this->migrarTabla('cortes_caja', $cajasExiste);
        }

        if ($ventasUsaCaja) {
            $this->migrarTabla('ventas', $cajasExiste);
        }

        Schema::dropIfExists('cajas');
    }

    public function down(): void
    {
        if (Schema::hasTable('cajas')) {
            return;
        }

        Schema::create('cajas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('id_sucursal')->constrained('sucursales')->cascadeOnDelete();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();
            $table->timestamps();
        });

        foreach (['cortes_caja', 'ventas'] as $tabla) {
            $this->restaurarTabla($tabla);
        }
    }

    /**
     * Agrega `id_sucursal`, la rellena desde `cajas`, valida que ningún
     * registro quede huérfano y retira `id_caja`.
     */
    private function migrarTabla(string $tabla, bool $cajasExiste): void
    {
        if (! $cajasExiste) {
            throw new RuntimeException(
                "No se puede migrar {$tabla}: la tabla cajas no existe y no hay origen para id_sucursal."
            );
        }

        if (! Schema::hasColumn($tabla, 'id_sucursal')) {
            Schema::table($tabla, function (Blueprint $table): void {
                $table->unsignedBigInteger('id_sucursal')->nullable()->after('id');
            });
        }

        DB::table($tabla)->update([
            'id_sucursal' => DB::raw(
                '(SELECT cajas.id_sucursal FROM cajas WHERE cajas.id = '.$tabla.'.id_caja)'
            ),
        ]);

        $huerfanos = DB::table($tabla)->whereNull('id_sucursal')->count();

        if ($huerfanos > 0) {
            throw new RuntimeException(
                "Hay {$huerfanos} registro(s) en {$tabla} sin sucursal asociada; la migración se detuvo antes de eliminar id_caja."
            );
        }

        Schema::table($tabla, function (Blueprint $table): void {
            $table->unsignedBigInteger('id_sucursal')->nullable(false)->change();
        });

        if (! $this->tieneForanea($tabla, 'id_sucursal')) {
            Schema::table($tabla, function (Blueprint $table): void {
                $table->foreign('id_sucursal')
                    ->references('id')
                    ->on('sucursales')
                    ->restrictOnDelete();
            });
        }

        $this->eliminarForanea($tabla, 'id_caja');

        if (Schema::hasColumn($tabla, 'id_caja')) {
            Schema::table($tabla, function (Blueprint $table): void {
                $table->dropColumn('id_caja');
            });
        }
    }

    /**
     * Reconstruye una caja por sucursal y vuelve a enlazar los registros.
     * La identidad original de cada caja no se conserva: dos cortes de la
     * misma sucursal comparten la caja reconstruida.
     */
    private function restaurarTabla(string $tabla): void
    {
        if (! Schema::hasColumn($tabla, 'id_sucursal')) {
            return;
        }

        $sucursales = DB::table($tabla)
            ->whereNotNull('id_sucursal')
            ->distinct()
            ->pluck('id_sucursal');

        foreach ($sucursales as $idSucursal) {
            if (! DB::table('cajas')->where('id_sucursal', $idSucursal)->exists()) {
                DB::table('cajas')->insert([
                    'id_sucursal' => $idSucursal,
                    'creado_en' => now(),
                    'actualizado_en' => now(),
                ]);
            }
        }

        if (! Schema::hasColumn($tabla, 'id_caja')) {
            Schema::table($tabla, function (Blueprint $table): void {
                $table->unsignedBigInteger('id_caja')->nullable()->after('id');
            });
        }

        DB::table($tabla)->update([
            'id_caja' => DB::raw(
                '(SELECT cajas.id FROM cajas WHERE cajas.id_sucursal = '.$tabla.'.id_sucursal)'
            ),
        ]);

        $huerfanos = DB::table($tabla)->whereNull('id_caja')->count();

        if ($huerfanos > 0) {
            throw new RuntimeException(
                "No se pudo reconstruir la caja de {$huerfanos} registro(s) en {$tabla}."
            );
        }

        Schema::table($tabla, function (Blueprint $table): void {
            $table->unsignedBigInteger('id_caja')->nullable(false)->change();
        });

        if (! $this->tieneForanea($tabla, 'id_caja')) {
            Schema::table($tabla, function (Blueprint $table): void {
                $table->foreign('id_caja')
                    ->references('id')
                    ->on('cajas')
                    ->cascadeOnDelete();
            });
        }

        $this->eliminarForanea($tabla, 'id_sucursal');

        if (Schema::hasColumn($tabla, 'id_sucursal')) {
            Schema::table($tabla, function (Blueprint $table): void {
                $table->dropColumn('id_sucursal');
            });
        }
    }

    private function tieneForanea(string $tabla, string $columna): bool
    {
        foreach (Schema::getForeignKeys($tabla) as $foranea) {
            if (in_array($columna, $foranea['columns'] ?? [], true)) {
                return true;
            }
        }

        return false;
    }

    private function eliminarForanea(string $tabla, string $columna): void
    {
        if (! $this->tieneForanea($tabla, $columna)) {
            return;
        }

        Schema::table($tabla, function (Blueprint $table) use ($columna): void {
            $table->dropForeign([$columna]);
        });
    }
};
