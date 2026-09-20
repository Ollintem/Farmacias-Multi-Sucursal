<?php

use App\Models\Sucursal;
use App\Models\User;

test('una sucursal puede registrarse con sus datos operativos', function () {
    $this->actingAs(User::factory()->create());

    $response = $this->post(route('sucursales.store'), [
        'nombre_sucursal' => 'Sucursal Reforma',
        'direccion' => 'Paseo de la Reforma 100',
        'telefono' => '5551234567',
        'correo_contacto' => 'reforma@farmacia.mx',
        'responsable' => 'Mariana López',
        'hora_apertura' => '08:00',
        'hora_cierre' => '21:00',
        'es_activa' => true,
    ]);

    $response->assertRedirect(route('sucursales.index'));
    $this->assertDatabaseHas('sucursales', [
        'nombre_sucursal' => 'Sucursal Reforma',
        'telefono' => '5551234567',
        'correo_contacto' => 'reforma@farmacia.mx',
        'responsable' => 'Mariana López',
        'es_activa' => true,
    ]);
});

test('una sucursal existente puede editarse y el formulario regresa al listado', function () {
    $this->actingAs(User::factory()->create());

    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Centro',
        'direccion' => 'Av. Central 123',
        'telefono' => '5550001111',
        'correo_contacto' => 'centro@farmacia.mx',
        'responsable' => 'Carlos Ruiz',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
        'es_activa' => true,
    ]);

    $editResponse = $this->get(route('sucursales.edit', $sucursal));
    $editResponse->assertOk();
    $editResponse->assertSee(route('sucursales.index'));

    $response = $this->put(route('sucursales.update', $sucursal), [
        'nombre_sucursal' => 'Sucursal Centro Renovada',
        'direccion' => 'Av. Central 456',
        'telefono' => '5559998888',
        'correo_contacto' => 'renovada@farmacia.mx',
        'responsable' => 'Laura Sánchez',
        'hora_apertura' => '07:30',
        'hora_cierre' => '22:00',
    ]);

    $response->assertRedirect(route('sucursales.index'));
    $this->assertDatabaseHas('sucursales', [
        'id' => $sucursal->id,
        'nombre_sucursal' => 'Sucursal Centro Renovada',
        'direccion' => 'Av. Central 456',
        'es_activa' => false,
    ]);
});
