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
            $table->integer('stock')->default(0);
            $table->double('precio');
            $table->foreignId('id_presentacion')->nullable()->constrained('presentaciones')->cascadeOnDelete();
            $table->boolean('es_controlado')->default(false);
            $table->timestamp('entregado_en')->nullable();
            $table->boolean('es_activo')->default(true);
            $table->timestamps();
        });

        // `lotes.id_producto` se declaró sin constraint en 000008 porque esta
        // tabla aún no existía. Ya creada, se agrega la FK aquí.
        Schema::table('lotes', function (Blueprint $table) {
            $table->foreign('id_producto')->references('id')->on('productos')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lotes', function (Blueprint $table) {
            $table->dropForeign(['id_producto']);
        });

        Schema::dropIfExists('productos');
    }
};
