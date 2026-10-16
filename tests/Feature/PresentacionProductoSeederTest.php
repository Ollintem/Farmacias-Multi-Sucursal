<?php

use App\Models\PresentacionProducto;
use Database\Seeders\PresentacionProductoSeeder;

test('crea las tres presentaciones base sin duplicados', function () {
    $this->seed(PresentacionProductoSeeder::class);

    $esperadas = [
        'blister',
        'caja',
        'unidad',
    ];

    expect(PresentacionProducto::count())->toBe(3)
        ->and(PresentacionProducto::pluck('presentacion')->sort()->values()->all())
        ->toEqualCanonicalizing($esperadas);

    $this->seed(PresentacionProductoSeeder::class);

    expect(PresentacionProducto::count())->toBe(3);
});
