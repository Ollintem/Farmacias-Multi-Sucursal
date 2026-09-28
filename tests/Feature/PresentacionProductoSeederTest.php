<?php

use App\Models\PresentacionProducto;
use Database\Seeders\PresentacionProductoSeeder;

test('crea las dos presentaciones base sin duplicados', function () {
    $this->seed(PresentacionProductoSeeder::class);

    $esperadas = [
        'blister',
        'caja',
    ];

    expect(PresentacionProducto::count())->toBe(2)
        ->and(PresentacionProducto::pluck('presentacion')->sort()->values()->all())
        ->toEqualCanonicalizing($esperadas);

    $this->seed(PresentacionProductoSeeder::class);

    expect(PresentacionProducto::count())->toBe(2);
});
