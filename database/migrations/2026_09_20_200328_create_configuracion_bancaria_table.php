<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion_bancaria', function (Blueprint $table) {
            $table->id();
            $table->string('banco', 100)->default('');
            $table->string('clabe', 20)->default('');
            $table->string('beneficiario', 150)->default('');
            $table->string('numero_cuenta', 30)->default('');
            $table->string('correo_contacto', 100)->default('');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_bancaria');
    }
};
