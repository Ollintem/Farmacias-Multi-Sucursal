<?php

use App\Models\Categoria;
use App\Models\Compra;
use App\Models\Inventario;
use App\Models\Lote;
use App\Models\PresentacionProducto;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;

function crearContextoCompra(object $test): array
{
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Compra',
        'direccion' => 'Av. Compra 1',
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
    session(['active_sucursal_id' => $sucursal->id]);

    $proveedor = Proveedor::create([
        'nombre_proveedor' => 'Proveedor Compra',
        'direccion' => 'Calle 1',
        'unidad_entrega' => 'Caja',
        'telefono' => '5551234567',
        'correo' => 'proveedor-compra@test.com',
    ]);

    $categoria = Categoria::create(['nombre' => 'Analgesicos']);
    $presentacion = PresentacionProducto::create(['presentacion' => 'Caja', 'descripcion' => '']);

    $producto = Producto::create([
        'codigo_barras' => '7501112223352',
        'nombre_producto' => 'Ibuprofeno 400mg',
        'descripcion' => '',
        'stock' => 0,
        'precio' => null,
        'id_presentacion' => $presentacion->id,
        'id_categoria' => $categoria->id,
        'es_controlado' => false,
        'es_activo' => true,
    ]);

    $producto->presentacionesPrecio()->create([
        'id_presentacion' => $presentacion->id,
        'unidades' => 10,
        'precio_presentacion' => 90,
    ]);

    return [$sucursal, $proveedor, $producto, $user];
}

test('el formulario de compra muestra productos, cantidades y fechas', function () {
    [$sucursal, $proveedor, $producto] = crearContextoCompra($this);

    $response = $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('compras.create'));

    $response->assertOk()
        ->assertSee('Lotes de la compra')
        ->assertSee('Cantidad')
        ->assertSee('Fecha de entrada')
        ->assertSee('Fecha de caducidad')
        ->assertSee($producto->nombre_producto);
});

test('la compra guarda sus lotes con fechas y convierte la presentacion', function () {
    [$sucursal, $proveedor, $producto] = crearContextoCompra($this);

    $response = $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->post(route('compras.store'), [
            'id_proveedor' => $proveedor->id,
            'id_sucursal' => $sucursal->id,
            'lotes' => [
                [
                    'id_producto' => $producto->id,
                    'cantidad' => 3,
                    'fecha_entrada' => '2026-10-01',
                    'fecha_caducidad' => '2027-10-01',
                ],
                [
                    'id_producto' => $producto->id,
                    'cantidad' => 2,
                    'fecha_entrada' => '2026-10-02',
                    'fecha_caducidad' => '2027-11-01',
                ],
            ],
        ]);

    $response->assertRedirect(route('entradas-de-almacen.index', [
        'sucursal' => $sucursal->id,
        'tipo' => 'compras',
    ]));

    $compra = Compra::latest('id')->first();

    expect($compra)->not->toBeNull()
        ->and($compra->estaRecibida())->toBeTrue()
        ->and((int) $compra->pedido_por)->toBe((int) auth()->id())
        ->and((int) $compra->recibido_por)->toBe((int) auth()->id())
        ->and($compra->entregado_en)->not->toBeNull()
        ->and($compra->lotes)->toHaveCount(2);

    // 3 cajas × 10 uds = 30 unidades y 2 cajas × 10 uds = 20.
    $this->assertDatabaseHas('lotes', [
        'id_compra' => $compra->id,
        'id_producto' => $producto->id,
        'stock_lote' => 30,
    ]);

    $this->assertDatabaseHas('lotes', [
        'id_compra' => $compra->id,
        'id_producto' => $producto->id,
        'stock_lote' => 20,
    ]);

    // El inventario se incorpora al registrar la compra.
    $this->assertDatabaseCount('inventario', 2);
});

test('la compra rechaza el guardado sin lotes', function () {
    [$sucursal, $proveedor] = crearContextoCompra($this);

    $response = $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->post(route('compras.store'), [
            'id_proveedor' => $proveedor->id,
            'id_sucursal' => $sucursal->id,
            'lotes' => [],
        ]);

    $response->assertSessionHasErrors('lotes');
    expect(Compra::count())->toBe(0);
});

test('la compra exige las fechas de entrada y caducidad de cada lote', function () {
    [$sucursal, $proveedor, $producto] = crearContextoCompra($this);

    $response = $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->post(route('compras.store'), [
            'id_proveedor' => $proveedor->id,
            'id_sucursal' => $sucursal->id,
            'lotes' => [
                ['id_producto' => $producto->id, 'cantidad' => 1],
            ],
        ]);

    $response->assertSessionHasErrors([
        'lotes.0.fecha_entrada',
        'lotes.0.fecha_caducidad',
    ]);

    expect(Compra::count())->toBe(0)
        ->and(Lote::count())->toBe(0);
});

test('la compra actualiza inventario y existencias de sus lotes al registrarse', function () {
    [$sucursal, $proveedor, $producto, $user] = crearContextoCompra($this);

    $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->post(route('compras.store'), [
            'id_proveedor' => $proveedor->id,
            'id_sucursal' => $sucursal->id,
            'lotes' => [
                [
                    'id_producto' => $producto->id,
                    'cantidad' => 3,
                    'fecha_entrada' => '2026-10-01',
                    'fecha_caducidad' => '2027-10-01',
                ],
            ],
        ])
        ->assertRedirect(route('entradas-de-almacen.index', [
            'sucursal' => $sucursal->id,
            'tipo' => 'compras',
        ]))
        ->assertSessionHas('success');

    $compra = Compra::latest('id')->firstOrFail();
    $lote = $compra->lotes()->firstOrFail();

    expect($compra->estaRecibida())->toBeTrue()
        ->and((int) $compra->recibido_por)->toBe((int) $user->id);

    $inventario = Inventario::where('id_sucursal', $sucursal->id)
        ->where('id_lote', $lote->id)
        ->firstOrFail();

    expect((int) $inventario->stock_individual)->toBe(30)
        ->and((int) $inventario->stock)->toBe(3)
        ->and($producto->fresh()->stockEnSucursal($sucursal->id))->toBe(30);

    $this->assertDatabaseHas('producto_sucursal', [
        'producto' => $producto->id,
        'id_sucursal' => $sucursal->id,
    ]);
});

test('el listado de entradas expresa las unidades en presentaciones principales', function () {
    [$sucursal, $proveedor, $producto] = crearContextoCompra($this);

    $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->post(route('compras.store'), [
            'id_proveedor' => $proveedor->id,
            'id_sucursal' => $sucursal->id,
            'lotes' => [
                [
                    'id_producto' => $producto->id,
                    'cantidad' => 3,
                    'fecha_entrada' => '2026-10-01',
                    'fecha_caducidad' => '2027-10-01',
                ],
                [
                    'id_producto' => $producto->id,
                    'cantidad' => 2,
                    'fecha_entrada' => '2026-10-02',
                    'fecha_caducidad' => '2027-11-01',
                ],
            ],
        ])
        ->assertRedirect();

    $response = $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('entradas-de-almacen.index', ['sucursal' => $sucursal->id, 'tipo' => 'compras']))
        ->assertOk()
        ->assertSee('Cajas');

    $entrada = $response->viewData('entradas')->first();

    expect((int) $entrada['unidades'])->toBe(50)
        ->and((int) $entrada['presentaciones'])->toBe(5)
        ->and($entrada['presentaciones_detalle'])->toBe([
            ['nombre' => 'Caja', 'cantidad' => 5],
        ]);
});
