<?php

use App\Models\Modulo;
use Database\Seeders\ModuloSeeder;

test('crea los once modulos base sin duplicados', function () {
    $this->seed(ModuloSeeder::class);

    $esperados = [
        'Dashboard',
        'Punto de venta',
        'Inventario',
        'Lotes y caducidades',
        'Entradas de almacén',
        'Traspasos',
        'Sucursales',
        'Usuarios y roles',
        'Caja',
        'Reportes',
        'Alertas',
    ];

    expect(Modulo::count())->toBe(11)
        ->and(Modulo::pluck('nombre_modulo')->sort()->values()->all())
        ->toEqualCanonicalizing($esperados);

    $this->seed(ModuloSeeder::class);

    expect(Modulo::count())->toBe(11);
});
