<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('traspasos', function (Blueprint $table) {
            $table->foreignId('id_producto')->nullable()->after('sucursal_b')->constrained('productos')->nullOnDelete();
            $table->integer('cantidad')->default(1)->after('id_producto');
            $table->text('mensaje')->nullable()->after('cantidad');
            $table->text('motivo_respuesta')->nullable()->after('mensaje');
            $table->timestamp('respondido_en')->nullable()->after('creado_en');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('traspasos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_producto');
            $table->dropColumn(['cantidad', 'mensaje', 'motivo_respuesta', 'respondido_en']);
        });
    }
};
