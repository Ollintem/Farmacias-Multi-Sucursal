<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalles_traspaso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('traspaso')->constrained('traspasos')->cascadeOnDelete();
            $table->foreignId('producto')->constrained('productos')->cascadeOnDelete();
            $table->integer('cantidad');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalles_traspaso');
    }
};
