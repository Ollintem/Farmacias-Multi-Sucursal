<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_proveedor', 20);
            $table->text('direccion');
            $table->string('unidad_entrega', 20);
            $table->string('telefono', 20);
            $table->string('correo', 30);
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();
            $table->timestamps();
        });

        Schema::create('modulos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_modulo', 20);
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();
            $table->timestamps();
        });

        Schema::create('presentaciones', function (Blueprint $table) {
            $table->id();
            $table->string('presentacion', 20);
            $table->text('descripcion');
        });

        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->double('monto');
            $table->string('metodo', 20);
            $table->string('estado', 20);
            $table->string('referencia', 50);
            $table->timestamp('creado_en')->useCurrent();
        });

        Schema::create('configuracion_bancaria', function (Blueprint $table) {
            $table->id();
            $table->string('banco', 100)->default('');
            $table->string('clabe', 20)->default('');
            $table->string('beneficiario', 150)->default('');
            $table->string('numero_cuenta', 30)->default('');
            $table->string('correo_contacto', 100)->default('');
            $table->timestamps();
        });

        Schema::create('cajas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_sucursal')->constrained('sucursales')->cascadeOnDelete();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cajas');
        Schema::dropIfExists('configuracion_bancaria');
        Schema::dropIfExists('pagos');
        Schema::dropIfExists('presentaciones');
        Schema::dropIfExists('modulos');
        Schema::dropIfExists('proveedores');
    }
};
