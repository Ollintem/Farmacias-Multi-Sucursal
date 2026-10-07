<?php

namespace Database\Seeders;

use App\Models\Modulo;
use App\Models\TipoAlerta;
use Illuminate\Database\Seeder;

class TipoAlertaSeeder extends Seeder
{
    /**
     * Catálogo inicial de tipos de alerta.
     *
     * Cada tipo se asocia a su módulo, define su nivel
     * y queda activo desde el inicio.
     */
    public function run(): void
    {
        $tipos = [
            ['nombre' => 'STOCK_BAJO', 'modulo' => 'Inventario', 'nivel' => TipoAlerta::NIVEL_ADVERTENCIA],
            ['nombre' => 'CADUCIDAD_PROXIMA', 'modulo' => 'Lotes y caducidades', 'nivel' => TipoAlerta::NIVEL_ADVERTENCIA],
            ['nombre' => 'PRODUCTO_CADUCADO', 'modulo' => 'Lotes y caducidades', 'nivel' => TipoAlerta::NIVEL_CRITICA],
            ['nombre' => 'TRASPASO_PENDIENTE', 'modulo' => 'Traspasos', 'nivel' => TipoAlerta::NIVEL_ADVERTENCIA],
            ['nombre' => 'TRASPASO_RECIBIDO', 'modulo' => 'Traspasos', 'nivel' => TipoAlerta::NIVEL_INFO],
            ['nombre' => 'TRASPASO_RECHAZADO', 'modulo' => 'Traspasos', 'nivel' => TipoAlerta::NIVEL_ADVERTENCIA],
        ];

        foreach ($tipos as $tipo) {
            $modulo = Modulo::firstOrCreate(['nombre_modulo' => $tipo['modulo']]);

            TipoAlerta::updateOrCreate(
                ['nombre' => $tipo['nombre']],
                [
                    'id_modulo' => $modulo->id,
                    'nivel' => $tipo['nivel'],
                    'activo' => true,
                ]
            );
        }
    }
}
