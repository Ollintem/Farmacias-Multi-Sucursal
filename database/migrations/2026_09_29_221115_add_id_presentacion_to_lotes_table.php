<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // La 000008 ya declara la columna en el archivo, pero las BD
        // migradas antes de 0a1530c no la tienen. El guard evita
        // duplicarla en BD frescas (tests) que sí la crean desde 000008.
        if (! Schema::hasColumn('lotes', 'id_presentacion')) {
            Schema::table('lotes', function (Blueprint $table) {
                $table->foreignId('id_presentacion')->nullable()->constrained('presentaciones')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('lotes', 'id_presentacion')) {
            Schema::table('lotes', function (Blueprint $table) {
                $table->dropForeign(['id_presentacion']);
                $table->dropColumn('id_presentacion');
            });
        }
    }
};
