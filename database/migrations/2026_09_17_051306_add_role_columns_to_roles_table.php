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
        Schema::table('roles', function (Blueprint $table) {
            if (! Schema::hasColumn('roles', 'tipo_rol')) {
                $table->string('tipo_rol')->after('id');
            }

            if (! Schema::hasColumn('roles', 'descripcion')) {
                $table->string('descripcion')->nullable()->after('tipo_rol');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('roles', 'tipo_rol')) {
                $columns[] = 'tipo_rol';
            }

            if (Schema::hasColumn('roles', 'descripcion')) {
                $columns[] = 'descripcion';
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
