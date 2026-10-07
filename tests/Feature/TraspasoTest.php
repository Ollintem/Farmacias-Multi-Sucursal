<?php

use App\Models\Categoria;
use App\Models\Inventario;
use App\Models\Lote;
use App\Models\Modulo;
use App\Models\PermisoActivado;
use App\Models\PresentacionProducto;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\Traspaso;
use App\Models\User;
use Carbon\Carbon;

function crearContextoTraspaso(object $test): array
{
    $origen = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Origen',
        'direccion' => 'Av. Origen 1',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $destino = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Destino',
        'direccion' => 'Av. Destino 1',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $rol = Rol::firstOrCreate(
        ['tipo_rol' => 'SuperAdmin'],
        ['descripcion' => 'Acceso total']
    );

    $user = User::factory()->create([
        'id_sucursal' => $origen->id,
        'id_rol' => $rol->id,
    ]);

    $test->actingAs($user);
    session(['active_sucursal_id' => $destino->id]);

    $categoria = Categoria::create(['nombre' => 'Analgesicos']);
    $presentacion = PresentacionProducto::create(['presentacion' => 'Caja', 'descripcion' => '']);

    $producto = Producto::create([
        'codigo_barras' => '7501112223369',
        'nombre_producto' => 'Ibuprofeno 400mg',
        'descripcion' => '',
        'precio' => null,
        'id_presentacion' => $presentacion->id,
        'id_categoria' => $categoria->id,
        'es_controlado' => false,
        'es_activo' => true,
    ]);

    $vigente = Lote::create([
        'folio' => 'LOTVIG001',
        'stock_lote' => 10,
        'id_producto' => $producto->id,
        'id_presentacion' => $presentacion->id,
        'entregado_en' => Carbon::now()->format('Y-m-d'),
        'fecha_caducidad' => Carbon::now()->addDays(60)->format('Y-m-d'),
    ]);

    Inventario::create([
        'id_sucursal' => $origen->id,
        'id_lote' => $vigente->id,
        'stock' => 10,
    ]);

    $caduco = Lote::create([
        'folio' => 'LOTCAD001',
        'stock_lote' => 5,
        'id_producto' => $producto->id,
        'id_presentacion' => $presentacion->id,
        'entregado_en' => Carbon::now()->subDays(60)->format('Y-m-d'),
        'fecha_caducidad' => Carbon::now()->subDays(10)->format('Y-m-d'),
    ]);

    Inventario::create([
        'id_sucursal' => $origen->id,
        'id_lote' => $caduco->id,
        'stock' => 5,
    ]);

    return [$origen, $destino, $producto, $vigente, $caduco];
}

test('el formulario de traspaso solo muestra lotes disponibles y no caducados', function () {
    [$origen, $destino] = crearContextoTraspaso($this);

    $response = $this->withSession(['active_sucursal_id' => $origen->id])
        ->get(route('traspasos.create'));

    $response->assertOk()
        ->assertSee('Lotes a traspasar')
        ->assertSee('LOTVIG001')
        ->assertDontSee('LOTCAD001');
});

test('el traspaso guarda lotes con cantidades en detalles_traspaso', function () {
    [$origen, $destino, $producto, $vigente] = crearContextoTraspaso($this);

    $response = $this->withSession(['active_sucursal_id' => $origen->id])
        ->post(route('traspasos.store'), [
            'sucursal_a' => $origen->id,
            'sucursal_b' => $destino->id,
            'estado' => 'pendiente',
            'lotes' => [
                ['lote' => $vigente->id, 'cantidad' => 3],
            ],
        ]);

    $response->assertRedirect(route('entradas-de-almacen.index', [
        'sucursal' => $destino->id,
        'tipo' => 'traspasos',
    ]));

    $traspaso = Traspaso::latest('id')->first();

    expect($traspaso)->not->toBeNull();
    $this->assertDatabaseHas('detalles_traspaso', [
        'traspaso' => $traspaso->id,
        'id_lote' => $vigente->id,
        'cantidad' => 3,
    ]);
});

test('el traspaso rechaza el guardado sin lotes', function () {
    [$origen, $destino] = crearContextoTraspaso($this);

    $response = $this->withSession(['active_sucursal_id' => $origen->id])
        ->post(route('traspasos.store'), [
            'sucursal_a' => $origen->id,
            'sucursal_b' => $destino->id,
            'estado' => 'pendiente',
            'lotes' => [],
        ]);

    $response->assertSessionHasErrors('lotes');
    expect(Traspaso::count())->toBe(0);
});

test('el traspaso rechaza lotes caducados', function () {
    [$origen, $destino, , , $caduco] = crearContextoTraspaso($this);

    $response = $this->withSession(['active_sucursal_id' => $origen->id])
        ->post(route('traspasos.store'), [
            'sucursal_a' => $origen->id,
            'sucursal_b' => $destino->id,
            'estado' => 'pendiente',
            'lotes' => [
                ['lote' => $caduco->id, 'cantidad' => 1],
            ],
        ]);

    $response->assertSessionHasErrors('lotes.0.lote');
    expect(Traspaso::count())->toBe(0);
});

test('el traspaso rechaza cantidades mayores al stock del origen', function () {
    [$origen, $destino, , $vigente] = crearContextoTraspaso($this);

    $response = $this->withSession(['active_sucursal_id' => $origen->id])
        ->post(route('traspasos.store'), [
            'sucursal_a' => $origen->id,
            'sucursal_b' => $destino->id,
            'estado' => 'pendiente',
            'lotes' => [
                ['lote' => $vigente->id, 'cantidad' => 99],
            ],
        ]);

    $response->assertSessionHasErrors('lotes.0.cantidad');
    expect(Traspaso::count())->toBe(0);
});

test('el traspaso rechaza destino igual al origen', function () {
    [$origen, , , $vigente] = crearContextoTraspaso($this);

    $response = $this->withSession(['active_sucursal_id' => $origen->id])
        ->post(route('traspasos.store'), [
            'sucursal_b' => $origen->id,
            'lotes' => [
                ['lote' => $vigente->id, 'cantidad' => 1],
            ],
        ]);

    $response->assertSessionHasErrors('sucursal_b');
    expect(Traspaso::count())->toBe(0);
});

test('el traspaso siempre nace pendiente desde la sucursal de sesion', function () {
    [$origen, $destino, , $vigente] = crearContextoTraspaso($this);

    $response = $this->withSession(['active_sucursal_id' => $origen->id])
        ->post(route('traspasos.store'), [
            'sucursal_a' => $destino->id,
            'sucursal_b' => $destino->id,
            'estado' => 'enviado',
            'lotes' => [
                ['lote' => $vigente->id, 'cantidad' => 2],
            ],
        ]);

    $response->assertRedirect(route('entradas-de-almacen.index', [
        'sucursal' => $destino->id,
        'tipo' => 'traspasos',
    ]));

    $traspaso = Traspaso::latest('id')->first();

    expect($traspaso)->not->toBeNull()
        ->and((int) $traspaso->sucursal_a)->toBe((int) $origen->id)
        ->and($traspaso->estado)->toBe('pendiente');
});

test('la sucursal origen cancela su traspaso pendiente', function () {
    [$origen, $destino] = crearContextoTraspaso($this);
    $usuario = User::where('id_sucursal', $origen->id)->first();

    $traspaso = Traspaso::create([
        'sucursal_a' => $origen->id,
        'sucursal_b' => $destino->id,
        'pedido_por' => $usuario->id,
        'estado' => 'pendiente',
    ]);

    $response = $this->actingAs($usuario)
        ->withSession(['active_sucursal_id' => $origen->id])
        ->post(route('traspasos.cancelar', $traspaso));

    $response->assertRedirect(route('entradas-de-almacen.index', [
        'sucursal' => $origen->id,
        'tipo' => 'traspasos',
    ]));

    expect($traspaso->fresh()->estado)->toBe('cancelado');
});

test('no se cancela un traspaso ya respondido', function () {
    [$origen, $destino] = crearContextoTraspaso($this);
    $usuario = User::where('id_sucursal', $origen->id)->first();

    $traspaso = Traspaso::create([
        'sucursal_a' => $origen->id,
        'sucursal_b' => $destino->id,
        'pedido_por' => $usuario->id,
        'estado' => 'aceptado',
    ]);

    $response = $this->actingAs($usuario)
        ->withSession(['active_sucursal_id' => $origen->id])
        ->post(route('traspasos.cancelar', $traspaso));

    $response->assertRedirect();
    $response->assertSessionHas('error');
    expect($traspaso->fresh()->estado)->toBe('aceptado');
});

test('solo la sucursal origen puede cancelar el traspaso', function () {
    [$origen, $destino] = crearContextoTraspaso($this);
    $solicitante = User::where('id_sucursal', $origen->id)->first();

    $traspaso = Traspaso::create([
        'sucursal_a' => $origen->id,
        'sucursal_b' => $destino->id,
        'pedido_por' => $solicitante->id,
        'estado' => 'pendiente',
    ]);

    $rolCajero = Rol::firstOrCreate(['tipo_rol' => 'Cajero'], ['descripcion' => 'Ventas']);
    $ajeno = User::factory()->create(['id_rol' => $rolCajero->id, 'id_sucursal' => $destino->id]);
    $modulo = Modulo::firstOrCreate(['nombre_modulo' => 'Entradas de almacén']);
    PermisoActivado::create([
        'id_modulo' => $modulo->id,
        'id_usuario' => $ajeno->id,
        'puede_ver' => true,
        'puede_crear' => false,
        'puede_editar' => true,
        'puede_borrar' => false,
    ]);

    $response = $this->actingAs($ajeno)
        ->withSession(['active_sucursal_id' => $destino->id])
        ->post(route('traspasos.cancelar', $traspaso));

    $response->assertRedirect();
    $response->assertSessionHas('error');
    expect($traspaso->fresh()->estado)->toBe('pendiente');
});

test('entradas separa solicitudes recibidas y traspasos solicitados', function () {
    [$origen, $destino] = crearContextoTraspaso($this);
    $usuario = User::where('id_sucursal', $origen->id)->first();

    $recibido = Traspaso::create([
        'sucursal_a' => $origen->id,
        'sucursal_b' => $destino->id,
        'pedido_por' => $usuario->id,
        'estado' => 'pendiente',
    ]);
    $solicitado = Traspaso::create([
        'sucursal_a' => $destino->id,
        'sucursal_b' => $origen->id,
        'pedido_por' => $usuario->id,
        'estado' => 'pendiente',
    ]);

    $contenido = $this->actingAs($usuario)
        ->withSession(['active_sucursal_id' => $destino->id])
        ->get(route('entradas-de-almacen.index', ['sucursal' => $destino->id, 'tipo' => 'traspasos']))
        ->assertOk()
        ->getContent();

    $posRecibido = strpos($contenido, "T-{$recibido->id}");
    $posSolicitado = strpos($contenido, "T-{$solicitado->id}");
    $posSolicitudes = strpos($contenido, 'Solicitudes de traspasos');
    $posSolicitados = strpos($contenido, 'Traspasos solicitados');

    expect($posRecibido)->not->toBeFalse()
        ->and($posSolicitado)->not->toBeFalse()
        ->and($posSolicitudes)->not->toBeFalse()
        ->and($posSolicitados)->not->toBeFalse()
        ->and($posSolicitudes < $posRecibido)->toBeTrue()
        ->and($posRecibido < $posSolicitados)->toBeTrue()
        ->and($posSolicitados < $posSolicitado)->toBeTrue();
});
