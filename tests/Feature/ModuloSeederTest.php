<?php

use App\Models\Modulo;
use App\Models\PermisoActivado;
use App\Models\Proveedor;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\ModuloSeeder;

test('crea los doce modulos base sin duplicados', function () {
    $this->seed(ModuloSeeder::class);

    $esperados = [
        'Dashboard',
        'Punto de venta',
        'Inventario',
        'Lotes y caducidades',
        'Entradas de almacén',
        'Proveedores',
        'Traspasos',
        'Sucursales',
        'Usuarios y roles',
        'Caja',
        'Reportes',
        'Alertas',
    ];

    expect(Modulo::count())->toBe(12)
        ->and(Modulo::pluck('nombre_modulo')->sort()->values()->all())
        ->toEqualCanonicalizing($esperados);

    $this->seed(ModuloSeeder::class);

    expect(Modulo::count())->toBe(12);
});

test('el seeder general corre con los campos actuales y sin el seeder de lotes', function () {
    $this->seed(DatabaseSeeder::class);

    expect(Modulo::count())->toBe(12)
        ->and(Modulo::where('nombre_modulo', 'Proveedores')->exists())->toBeTrue()
        ->and(Proveedor::count())->toBe(3)
        ->and(User::count())->toBe(2);
});

test('el admin queda con acceso total al modulo Proveedores', function () {
    $this->seed(DatabaseSeeder::class);

    $admin = User::where('email', 'admin@example.com')->firstOrFail();
    $proveedores = Modulo::where('nombre_modulo', 'Proveedores')->firstOrFail();

    $permiso = PermisoActivado::where('id_usuario', $admin->id)
        ->where('id_modulo', $proveedores->id)
        ->firstOrFail();

    expect($permiso->puede_ver)->toBeTrue()
        ->and($permiso->puede_crear)->toBeTrue()
        ->and($permiso->puede_editar)->toBeTrue()
        ->and($permiso->puede_borrar)->toBeTrue();
});
