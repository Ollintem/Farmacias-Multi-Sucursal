<?php

use App\Models\Pedido;
use App\Models\Proveedor;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function fotoFalsa(string $nombre): UploadedFile
{
    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
    $ruta = tempnam(sys_get_temp_dir(), 'foto').'.png';
    file_put_contents($ruta, $png);

    return new UploadedFile($ruta, $nombre, 'image/png', null, true);
}

function crearContextoProveedores(object $test): array
{
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Proveedores',
        'direccion' => 'Av. Proveedores 1',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $rol = Rol::firstOrCreate(
        ['tipo_rol' => 'SuperAdmin'],
        ['descripcion' => 'Acceso total']
    );

    $user = User::factory()->create([
        'id_sucursal' => $sucursal->id,
        'id_rol' => $rol->id,
    ]);

    $test->actingAs($user);

    $proveedor = Proveedor::create([
        'nombre_proveedor' => 'Proveedor Test',
        'direccion' => 'Calle Test 1',
        'unidad_entrega' => 'Caja',
        'telefono' => '5551234567',
        'correo' => 'proveedor@test.com',
    ]);

    return [$sucursal, $proveedor, $user];
}

test('el listado muestra los proveedores con sus datos de contacto', function () {
    [, $proveedor] = crearContextoProveedores($this);

    $this->get(route('proveedores.index'))
        ->assertOk()
        ->assertSee($proveedor->nombre_proveedor)
        ->assertSee($proveedor->direccion)
        ->assertSee(route('proveedores.destroy', $proveedor), false);
});

test('eliminar quita al proveedor sin pedidos del directorio', function () {
    [, $proveedor] = crearContextoProveedores($this);

    $response = $this->delete(route('proveedores.destroy', $proveedor));

    $response->assertRedirect()
        ->assertSessionHas('success');

    expect(Proveedor::find($proveedor->id))->toBeNull();
});

test('eliminar se bloquea cuando el proveedor tiene pedidos', function () {
    [$sucursal, $proveedor, $user] = crearContextoProveedores($this);

    Pedido::create([
        'id_proveedor' => $proveedor->id,
        'id_sucursal' => $sucursal->id,
        'pedido_por' => $user->id,
        'estado' => 'pendiente',
    ]);

    $response = $this->delete(route('proveedores.destroy', $proveedor));

    $response->assertRedirect()
        ->assertSessionHas('error');

    expect(Proveedor::find($proveedor->id))->not->toBeNull();
});

test('el alta guarda foto, rfc y contacto con textos amplios', function () {
    crearContextoProveedores($this);

    $foto = fotoFalsa('logo.png');

    $response = $this->post(route('proveedores.store'), [
        'nombre_proveedor' => 'Distribuidora Farmacéutica del Centro con nombre largo',
        'direccion' => 'Av. Larga 123',
        'unidad_entrega' => 'Caja con presentación',
        'telefono' => '5551234567',
        'correo' => 'contacto@distribuidora.test',
        'rfc' => 'DFC120315KT1',
        'nombre_contacto' => 'María González',
        'es_activo' => '1',
        'foto' => $foto,
    ]);

    $response->assertRedirect(route('proveedores.index'))
        ->assertSessionHas('success');

    $proveedor = Proveedor::where('correo', 'contacto@distribuidora.test')->first();

    expect($proveedor)->not->toBeNull()
        ->and($proveedor->rfc)->toBe('DFC120315KT1')
        ->and($proveedor->nombre_contacto)->toBe('María González')
        ->and($proveedor->es_activo)->toBeTrue()
        ->and($proveedor->foto_path)->not->toBeNull();

    Storage::disk('public')->assertExists($proveedor->foto_path);
});

test('el alta rechaza correo duplicado y rfc inválido', function () {
    crearContextoProveedores($this);

    $base = [
        'nombre_proveedor' => 'Otro Proveedor',
        'direccion' => 'Calle 2',
        'unidad_entrega' => 'Pieza',
        'telefono' => '5559998888',
        'nombre_contacto' => null,
        'es_activo' => '1',
    ];

    $this->post(route('proveedores.store'), array_merge($base, [
        'correo' => 'proveedor@test.com',
        'rfc' => 'ABC123',
    ]))->assertSessionHasErrors(['correo', 'rfc']);
});

test('actualizar reemplaza la foto y borra el archivo anterior', function () {
    crearContextoProveedores($this);

    $proveedor = Proveedor::where('correo', 'proveedor@test.com')->first();

    $this->post(route('proveedores.store'), [
        'nombre_proveedor' => 'Con Foto',
        'direccion' => 'Calle F',
        'unidad_entrega' => 'Caja',
        'telefono' => '5550001111',
        'correo' => 'confoto@test.com',
        'foto' => fotoFalsa('vieja.png'),
    ])->assertRedirect();

    $proveedor = Proveedor::where('correo', 'confoto@test.com')->first();
    $fotoVieja = $proveedor->foto_path;

    $this->put(route('proveedores.update', $proveedor), [
        'nombre_proveedor' => 'Con Foto',
        'direccion' => 'Calle F',
        'unidad_entrega' => 'Caja',
        'telefono' => '5550001111',
        'correo' => 'confoto@test.com',
        'foto' => fotoFalsa('nueva.png'),
    ])->assertRedirect();

    $proveedor->refresh();

    expect($proveedor->foto_path)->not->toBe($fotoVieja);

    Storage::disk('public')->assertMissing($fotoVieja);
    Storage::disk('public')->assertExists($proveedor->foto_path);
});
