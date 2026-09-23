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
