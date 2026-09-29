<?php

use App\Models\Proveedor;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;

it('loads the lotes and expiry page', function () {
    $rol = Rol::firstOrCreate(['tipo_rol' => 'SuperAdmin'], ['descripcion' => 'Acceso total']);

    $user = User::factory()->create(['id_rol' => $rol->id]);

    $this->actingAs($user)
        ->get(route('lotes.index'))
        ->assertOk();
});

it('loads the lote creation form with pedido-driven proveedor and sucursal selects', function () {
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Lotes Create',
        'direccion' => 'Calle 1',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $rol = Rol::firstOrCreate(['tipo_rol' => 'SuperAdmin'], ['descripcion' => 'Acceso total']);

    $user = User::factory()->create([
        'id_rol' => $rol->id,
        'id_sucursal' => $sucursal->id,
    ]);

    Proveedor::create([
        'nombre_proveedor' => 'Proveedor Demo',
        'direccion' => 'Calle 2',
        'telefono' => '5550000000',
        'correo' => 'demo@proveedor.com',
        'unidad_entrega' => 'caja',
    ]);

    $contenido = $this->actingAs($user)
        ->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('lotes.create'))
        ->assertOk()
        ->getContent();

    expect($contenido)
        ->toContain('<input type="hidden" name="id_proveedor"')
        ->toContain('<input type="hidden" name="sucursal"')
        ->toContain('<input type="hidden" name="id_presentacion"')
        ->toContain('Proveedor Demo')
        ->toContain('Lotes Create');
});
