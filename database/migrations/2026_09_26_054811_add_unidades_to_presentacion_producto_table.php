<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presentacion_producto', function (Blueprint $table) {
            $table->integer('unidades')->default(1)->after('precio_presentacion');
        });
    }

    public function down(): void
    {
        Schema::table('presentacion_producto', function (Blueprint $table) {
            $table->dropColumn('unidades');
        });
    }
};
