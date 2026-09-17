<?php

use App\Models\PresentacionProducto;
use Database\Seeders\PresentacionProductoSeeder;

test('crea las cuatro presentaciones base sin duplicados', function () {
    $this->seed(PresentacionProductoSeeder::class);

    $esperadas = [
        'caja',
        'blister',
        'ampolleta',
        'pastilla',
    ];

    expect(PresentacionProducto::count())->toBe(4)
        ->and(PresentacionProducto::pluck('presentacion')->sort()->values()->all())
        ->toEqualCanonicalizing($esperadas);

    $this->seed(PresentacionProductoSeeder::class);

    expect(PresentacionProducto::count())->toBe(4);
});
