<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_barras', 20);
            $table->string('nombre_producto', 120);
            $table->text('descripcion');
            $table->integer('stock');
            $table->double('precio');
            $table->foreignId('id_lote')->nullable()->constrained('lotes')->cascadeOnDelete();
            $table->foreignId('id_presentacion')->nullable()->constrained('presentaciones')->cascadeOnDelete();
            $table->boolean('es_controlado')->default(false);
            $table->timestamp('entregado_en')->nullable();
            $table->boolean('es_activo')->default(true);
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
