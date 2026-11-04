<?php

use App\Models\Categoria;
use App\Models\Compra;
use App\Models\Inventario;
use App\Models\Lote;
use App\Models\PresentacionProducto;
use App\Models\Producto;
use App\Models\ProductoPresentacion;
use App\Models\Proveedor;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;

function contextoBusquedaInventario(object $test): array
{
    $sucursalA = Sucursal::create([
        'nombre_sucursal' => 'Sucursal A',
        'direccion' => 'Av. A 1',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $sucursalB = Sucursal::create([
        'nombre_sucursal' => 'Sucursal B',
        'direccion' => 'Av. B 2',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $rol = Rol::firstOrCreate(
        ['tipo_rol' => 'SuperAdmin'],
        ['descripcion' => 'Acceso total']
    );

    $user = User::factory()->create([
        'id_sucursal' => $sucursalA->id,
        'id_rol' => $rol->id,
    ]);

    $categoria = Categoria::create(['nombre' => 'Analgesicos']);
    $caja = PresentacionProducto::create(['presentacion' => 'Caja', 'descripcion' => '']);

    $paracetamol = Producto::create([
        'codigo_barras' => '7500000000219',
        'nombre_producto' => 'Paracetamol 500mg',
        'descripcion' => '',
        'stock' => 0,
        'precio' => 2.5,
        'id_presentacion' => $caja->id,
        'id_categoria' => $categoria->id,
        'es_controlado' => false,
        'es_activo' => true,
    ]);

    $ibuprofeno = Producto::create([
        'codigo_barras' => '7500000000226',
        'nombre_producto' => 'Ibuprofeno 400mg',
        'descripcion' => '',
        'stock' => 0,
        'precio' => 3.0,
        'id_presentacion' => $caja->id,
        'id_categoria' => $categoria->id,
        'es_controlado' => false,
        'es_activo' => true,
    ]);

    // Ambos productos están dados de alta en la sucursal A (pivot).
    $paracetamol->sucursales()->attach($sucursalA->id);
    $ibuprofeno->sucursales()->attach($sucursalA->id);

    $lote = Lote::create([
        'folio' => 'L-BUSQ-1',
        'stock_lote' => 6,
        'id_producto' => $paracetamol->id,
        'fecha_caducidad' => '2027-01-01',
    ]);

    Inventario::create([
        'id_sucursal' => $sucursalA->id,
        'id_lote' => $lote->id,
        'stock_individual' => 6,
    ]);

    // El mismo lote existe en la sucursal B sin existencias: Stock muestra 0.
    Inventario::create([
        'id_sucursal' => $sucursalB->id,
        'id_lote' => $lote->id,
        'stock_individual' => 0,
    ]);

    // Ibuprofeno también tiene inventario en la sucursal A (4 uds.).
    $loteIbuprofeno = Lote::create([
        'folio' => 'L-BUSQ-IBU',
        'stock_lote' => 4,
        'id_producto' => $ibuprofeno->id,
        'fecha_caducidad' => '2027-02-01',
    ]);

    Inventario::create([
        'id_sucursal' => $sucursalA->id,
        'id_lote' => $loteIbuprofeno->id,
        'stock_individual' => 4,
    ]);

    $test->actingAs($user);

    return [$sucursalA, $sucursalB];
}

test('la pestana stock filtra por codigo de barras', function () {
    [$sucursalA] = contextoBusquedaInventario($this);

    $this->withSession(['active_sucursal_id' => $sucursalA->id])
        ->get(route('inventario.stock', ['buscar' => '7500000000219']))
        ->assertOk()
        ->assertSee('Paracetamol 500mg')
        ->assertDontSee('Ibuprofeno 400mg');
});

test('la pestana stock filtra por nombre de producto', function () {
    [$sucursalA] = contextoBusquedaInventario($this);

    $this->withSession(['active_sucursal_id' => $sucursalA->id])
        ->get(route('inventario.stock', ['buscar' => 'ibuprofeno']))
        ->assertOk()
        ->assertSee('Ibuprofeno 400mg')
        ->assertDontSee('Paracetamol 500mg');
});

test('la pestana stock muestra el stock de la sucursal seleccionada', function () {
    [$sucursalA, $sucursalB] = contextoBusquedaInventario($this);

    $this->withSession(['active_sucursal_id' => $sucursalA->id])
        ->get(route('inventario.stock'))
        ->assertOk()
        ->assertSee('Paracetamol 500mg')
        ->assertSee('6 uds.', false);

    // El mismo producto no tiene inventario en la sucursal B: muestra 0.
    $this->withSession(['active_sucursal_id' => $sucursalB->id])
        ->get(route('inventario.stock'))
        ->assertOk()
        ->assertSee('Paracetamol 500mg')
        ->assertSee('0 uds.', false);
});

test('la pestana productos filtra por codigo y por nombre', function () {
    [$sucursalA] = contextoBusquedaInventario($this);

    $this->withSession(['active_sucursal_id' => $sucursalA->id])
        ->get(route('inventario.productos', ['buscar' => '7500000000226']))
        ->assertOk()
        ->assertSee('Ibuprofeno 400mg')
        ->assertDontSee('Paracetamol 500mg');

    $this->withSession(['active_sucursal_id' => $sucursalA->id])
        ->get(route('inventario.productos', ['buscar' => 'paracetamol']))
        ->assertOk()
        ->assertSee('Paracetamol 500mg')
        ->assertDontSee('Ibuprofeno 400mg');
});

test('la compra vincula el lote y el inventario a la sucursal al registrarla', function () {
    [$sucursalA] = contextoBusquedaInventario($this);

    $proveedor = Proveedor::create([
        'nombre_proveedor' => 'Proveedor Busq',
        'direccion' => 'Calle 1',
        'unidad_entrega' => 'Caja',
        'telefono' => '5550001',
        'correo' => 'busq@example.com',
    ]);

    $caja = PresentacionProducto::where('presentacion', 'Caja')->firstOrFail();

    $producto = Producto::where('codigo_barras', '7500000000219')->firstOrFail();

    ProductoPresentacion::create([
        'id_presentacion' => $caja->id,
        'producto' => $producto->id,
        'precio_presentacion' => 25.0,
        'unidades' => 10,
    ]);

    $this->withSession(['active_sucursal_id' => $sucursalA->id])
        ->post(route('compras.store'), [
            'id_proveedor' => $proveedor->id,
            'id_sucursal' => $sucursalA->id,
            'lotes' => [
                [
                    'id_producto' => $producto->id,
                    'cantidad' => 10,
                    'fecha_entrada' => '2026-09-01',
                    'fecha_caducidad' => '2027-09-01',
                ],
            ],
        ])
        ->assertRedirect(route('entradas-de-almacen.index', [
            'sucursal' => $sucursalA->id,
            'tipo' => 'compras',
        ]));

    $compra = Compra::latest('id')->firstOrFail();
    $lote = $compra->lotes()->firstOrFail();

    // 10 Cajas x 10 uds = 100 unidades; total = 6 del lote previo + 100 del nuevo.
    expect((int) $lote->id_producto)->toBe((int) $producto->id)
        ->and($lote->compra?->proveedor?->nombre_proveedor)->toBe('Proveedor Busq')
        ->and((int) $lote->stock_lote)->toBe(100);

    $this->assertDatabaseHas('inventario', [
        'id_sucursal' => $sucursalA->id,
        'id_lote' => $lote->id,
        'stock_individual' => 100,
    ]);

    expect($producto->fresh()->stockTotal())->toBe(106);

    // El proveedor del lote se resuelve vía compra en el listado.
    $this->withSession(['active_sucursal_id' => $sucursalA->id])
        ->get(route('lotes.index', ['buscar' => $lote->folio]))
        ->assertOk()
        ->assertSee('Proveedor Busq');
});

test('el formulario de compra ofrece los productos', function () {
    [$sucursalA] = contextoBusquedaInventario($this);

    $this->withSession(['active_sucursal_id' => $sucursalA->id])
        ->get(route('compras.create'))
        ->assertOk()
        ->assertSee('name="lotes[0][id_producto]"', false)
        ->assertSee('Paracetamol 500mg')
        ->assertSee('Ibuprofeno 400mg');
});

test('la compra rechaza la caducidad anterior a la fecha de entrada', function () {
    [$sucursalA] = contextoBusquedaInventario($this);

    $proveedor = Proveedor::create([
        'nombre_proveedor' => 'Proveedor Busq',
        'direccion' => 'Calle 1',
        'unidad_entrega' => 'Caja',
        'telefono' => '5550001',
        'correo' => 'busq@example.com',
    ]);

    $producto = Producto::where('codigo_barras', '7500000000219')->firstOrFail();

    $this->withSession(['active_sucursal_id' => $sucursalA->id])
        ->post(route('compras.store'), [
            'id_proveedor' => $proveedor->id,
            'id_sucursal' => $sucursalA->id,
            'lotes' => [
                [
                    'id_producto' => $producto->id,
                    'cantidad' => 1,
                    'fecha_entrada' => '2027-09-01',
                    'fecha_caducidad' => '2026-09-01',
                ],
            ],
        ])
        ->assertSessionHasErrors('lotes.0.fecha_caducidad');

    $this->assertDatabaseCount('compras', 0);
});
