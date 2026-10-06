<?php

use App\Models\Categoria;
use App\Models\Inventario;
use App\Models\Lote;
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

    $response = $this->withSession(['active_sucursal_id' => $destino->id])
        ->get(route('traspasos.create'));

    $response->assertOk()
        ->assertSee('Lotes a traspasar')
        ->assertSee('LOTVIG001')
        ->assertDontSee('LOTCAD001');
});

test('el traspaso guarda lotes con cantidades en detalles_traspaso', function () {
    [$origen, $destino, $producto, $vigente] = crearContextoTraspaso($this);

    $response = $this->withSession(['active_sucursal_id' => $destino->id])
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

    $response = $this->withSession(['active_sucursal_id' => $destino->id])
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

    $response = $this->withSession(['active_sucursal_id' => $destino->id])
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

    $response = $this->withSession(['active_sucursal_id' => $destino->id])
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
