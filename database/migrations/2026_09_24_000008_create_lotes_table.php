<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lotes', function (Blueprint $table) {
            $table->id();
            $table->string('folio', 20);
            $table->integer('stock_lote')->default(0);
            $table->foreignId('id_pedido')->nullable()->constrained('pedidos')->nullOnDelete();
            $table->foreignId('id_proveedor')->nullable()->constrained('proveedores')->cascadeOnDelete();
            // Columna sin constraint inline: `productos` aún no existe (se crea en
            // 000009). La FK se agrega en 000009 después de crear `productos`.
            $table->foreignId('id_producto')->nullable();
            $table->timestamp('entregado_en')->useCurrent();
            $table->timestamp('fecha_caducidad')->nullable();
            $table->timestamp('fecha_de_caducidad')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lotes');
    }
};
