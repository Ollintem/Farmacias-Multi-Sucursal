<?php

use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;

test('una sucursal puede registrarse con sus datos operativos', function () {
    $this->actingAs(User::factory()->create());

    $response = $this->post(route('sucursales.store'), [
        'nombre_sucursal' => 'Sucursal Reforma',
        'direccion' => 'Paseo de la Reforma 100',
        'telefono' => '5551234567',
        'correo_contacto' => 'reforma@farmacia.mx',
        'hora_apertura' => '08:00',
        'hora_cierre' => '21:00',
        'es_activa' => true,
    ]);

    $response->assertRedirect(route('sucursales.index'));
    $this->assertDatabaseHas('sucursales', [
        'nombre_sucursal' => 'Sucursal Reforma',
        'telefono' => '5551234567',
        'correo_contacto' => 'reforma@farmacia.mx',
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
        'hora_apertura' => '07:30',
        'hora_cierre' => '22:00',
    ]);

    $response->assertRedirect(route('sucursales.index'));
    $this->assertDatabaseHas('sucursales', [
        'id' => $sucursal->id,
        'nombre_sucursal' => 'Sucursal Centro Renovada',
        'direccion' => 'Av. Central 456',
        'es_activa' => true,
    ]);
});

function crearContextoSucursales(object $test): array
{
    $rol = Rol::firstOrCreate(
        ['tipo_rol' => 'SuperAdmin'],
        ['descripcion' => 'Acceso total']
    );

    $admin = User::factory()->create(['id_rol' => $rol->id]);

    $test->actingAs($admin);

    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Test',
        'direccion' => 'Av. Test 1',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
        'es_activa' => true,
    ]);

    return [$sucursal, $admin, $rol];
}

test('el listado muestra las sucursales con su boton de eliminar', function () {
    [$sucursal] = crearContextoSucursales($this);

    $this->get(route('sucursales.index'))
        ->assertOk()
        ->assertSee($sucursal->nombre_sucursal)
        ->assertSee(route('sucursales.destroy', $sucursal), false)
        ->assertSee('data-flux-switch', false)
        ->assertSee('botonCancelar', false)
        ->assertDontSee('@if(')
        ->assertDontSee('@endif');
});

test('eliminar quita la sucursal sin movimiento del directorio', function () {
    [$sucursal] = crearContextoSucursales($this);

    $response = $this->delete(route('sucursales.destroy', $sucursal));

    $response->assertRedirect()
        ->assertSessionHas('success');

    expect(Sucursal::find($sucursal->id))->toBeNull();
});

test('eliminar se bloquea cuando la sucursal tiene usuarios', function () {
    [$sucursal, , $rol] = crearContextoSucursales($this);

    User::factory()->create([
        'id_sucursal' => $sucursal->id,
        'id_rol' => $rol->id,
    ]);

    $response = $this->delete(route('sucursales.destroy', $sucursal));

    $response->assertRedirect()
        ->assertSessionHas('error');

    expect(Sucursal::find($sucursal->id))->not->toBeNull();
});

test('editar los datos de una sucursal inactiva no cambia su estado', function () {
    $this->actingAs(User::factory()->create());

    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Norte',
        'direccion' => 'Blvd. Norte 10',
        'hora_apertura' => '09:00',
        'hora_cierre' => '19:00',
        'es_activa' => false,
    ]);

    $this->put(route('sucursales.update', $sucursal), [
        'nombre_sucursal' => 'Sucursal Norte Editada',
        'direccion' => 'Blvd. Norte 20',
        'hora_apertura' => '09:00',
        'hora_cierre' => '21:00',
    ])->assertRedirect(route('sucursales.index'));

    $actual = Sucursal::find($sucursal->id);
    expect($actual->nombre_sucursal)->toBe('Sucursal Norte Editada');
    expect($actual->es_activa)->toBeFalse();
});

test('el interruptor de estado desactiva una sucursal', function () {
    [$sucursal] = crearContextoSucursales($this);

    $this->patch(route('sucursales.estado', $sucursal), ['es_activa' => 0])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(Sucursal::find($sucursal->id)->es_activa)->toBeFalse();
});

test('el interruptor de estado reactiva una sucursal', function () {
    [$sucursal] = crearContextoSucursales($this);
    $sucursal->update(['es_activa' => false]);

    $this->patch(route('sucursales.estado', $sucursal), ['es_activa' => 1])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(Sucursal::find($sucursal->id)->es_activa)->toBeTrue();
});

test('el formulario de sucursal ya no muestra el checkbox de sucursal activa', function () {
    [$sucursal] = crearContextoSucursales($this);

    $this->get(route('sucursales.create'))
        ->assertOk()
        ->assertDontSee('Sucursal activa');

    $this->get(route('sucursales.edit', $sucursal))
        ->assertOk()
        ->assertDontSee('Sucursal activa');
});

test('las sucursales inactivas no aparecen en el selector de sucursales', function () {
    [$sucursal] = crearContextoSucursales($this);

    Sucursal::create([
        'nombre_sucursal' => 'Sucursal Cerrada',
        'direccion' => 'Calle Cerrada 1',
        'hora_apertura' => '08:00',
        'hora_cierre' => '14:00',
        'es_activa' => false,
    ]);

    $this->get(route('sucursales.create'))
        ->assertOk()
        ->assertSee($sucursal->nombre_sucursal)
        ->assertDontSee('Sucursal Cerrada');
});

test('no se puede elegir una sucursal inactiva desde la URL', function () {
    [$sucursal] = crearContextoSucursales($this);

    $cerrada = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Cerrada',
        'direccion' => 'Calle Cerrada 1',
        'hora_apertura' => '08:00',
        'hora_cierre' => '14:00',
        'es_activa' => false,
    ]);

    $this->get(route('sucursales.create', ['sucursal' => $cerrada->id]))
        ->assertOk()
        ->assertSee($sucursal->nombre_sucursal);

    expect((int) session('active_sucursal_id'))->not->toBe($cerrada->id);
});
