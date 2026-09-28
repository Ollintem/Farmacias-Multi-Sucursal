<?php

use App\Models\Categoria;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;

function usuarioConPermisoInventario(): User
{
    $sucursal = Sucursal::firstOrCreate(
        ['nombre_sucursal' => 'Sucursal Test'],
        ['direccion' => 'Calle 1', 'hora_apertura' => '08:00', 'hora_cierre' => '20:00']
    );

    $rol = Rol::firstOrCreate(
        ['tipo_rol' => 'SuperAdmin'],
        ['descripcion' => 'Acceso total']
    );

    return User::factory()->create([
        'id_sucursal' => $sucursal->id,
        'id_rol' => $rol->id,
    ]);
}

test('se puede crear una categoria nueva', function () {
    $this->actingAs(usuarioConPermisoInventario());

    $response = $this->postJson(route('categorias.store'), [
        'nombre' => 'Dermatológicos',
    ]);

    $response->assertOk();
    $response->assertJson(['nombre' => 'Dermatológicos']);

    $this->assertDatabaseHas('categorias', ['nombre' => 'Dermatológicos']);
});

test('no se permiten categorias duplicadas', function () {
    Categoria::create(['nombre' => 'Analgésicos']);

    $this->actingAs(usuarioConPermisoInventario());

    $response = $this->postJson(route('categorias.store'), [
        'nombre' => 'Analgésicos',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('nombre');
});

test('se puede crear una presentacion nueva', function () {
    $this->actingAs(usuarioConPermisoInventario());

    $response = $this->postJson(route('presentaciones.store'), [
        'presentacion' => 'Sobres',
    ]);

    $response->assertOk();
    $response->assertJson(['presentacion' => 'Sobres']);

    $this->assertDatabaseHas('presentaciones', ['presentacion' => 'Sobres']);
});

test('no se permiten presentaciones duplicadas', function () {
    $this->actingAs(usuarioConPermisoInventario());

    $this->postJson(route('presentaciones.store'), ['presentacion' => 'Vial'])->assertOk();

    $response = $this->postJson(route('presentaciones.store'), ['presentacion' => 'Vial']);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('presentacion');
});
