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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->text('codigo_barras');
            $table->string('nombre_producto', 20);
            $table->text('descripcion');
            $table->integer('stock');
            $table->double('precio');
            $table->foreignId('id_lote')->constrained('lotes')->cascadeOnDelete();
            $table->foreignId('id_presentacion')->constrained('presentacion_productos')->cascadeOnDelete();
            $table->boolean('es_controlado')->default(false);
            $table->boolean('es_activo')->default(true);
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
