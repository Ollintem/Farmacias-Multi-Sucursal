<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sucursales', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_sucursal');
            $table->string('direccion');
            $table->string('telefono', 30)->nullable();
            $table->string('correo_contacto', 150)->nullable();
            $table->string('responsable', 150)->nullable();
            $table->time('hora_apertura');
            $table->time('hora_cierre');
            $table->boolean('es_activa')->default(true);
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->timestamp('fecha_actualizacion')->useCurrent()->useCurrentOnUpdate();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sucursales');
    }
};
