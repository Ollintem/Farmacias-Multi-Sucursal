<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cortes_ciegos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuarios')->constrained('usuarios')->cascadeOnDelete();
            $table->boolean('corte_ciego')->default(false);
            $table->unique('id_usuarios');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cortes_ciegos');
    }
};
