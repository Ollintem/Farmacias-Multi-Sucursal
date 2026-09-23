<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_caja')->constrained('cajas')->cascadeOnDelete();
            $table->string('folio', 20);
            $table->double('descuento');
            $table->double('total');
            $table->foreignId('id_pago')->constrained('pagos')->cascadeOnDelete();
            $table->string('estado', 20);
            $table->timestamp('creado_en')->useCurrent();
        });

        Schema::create('producto_venta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta')->constrained('ventas')->cascadeOnDelete();
            $table->foreignId('producto')->constrained('productos')->cascadeOnDelete();
            $table->integer('cantidad');
            $table->double('precio_unidad');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_venta');
        Schema::dropIfExists('ventas');
    }
};
