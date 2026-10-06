<?php

use App\Models\Categoria;
use App\Models\Inventario;
use App\Models\Lote;
use App\Models\Pedido;
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

    Producto::create([
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

    $lote = Lote::create([
        'folio' => 'L-BUSQ-1',
        'stock_lote' => 6,
        'id_producto' => $paracetamol->id,
        'fecha_caducidad' => '2027-01-01',
    ]);

    Inventario::create([
        'id_sucursal' => $sucursalA->id,
        'id_lote' => $lote->id,
        'stock' => 6,
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

test('el alta de lote vincula el inventario a la sucursal', function () {
    [$sucursalA] = contextoBusquedaInventario($this);

    $proveedor = Proveedor::create([
        'nombre_proveedor' => 'Proveedor Busq',
        'direccion' => 'Calle 1',
        'unidad_entrega' => 'Caja',
        'telefono' => '5550001',
        'correo' => 'busq@example.com',
    ]);

    $pedido = Pedido::create([
        'id_proveedor' => $proveedor->id,
        'id_sucursal' => $sucursalA->id,
        'pedido_por' => auth()->id(),
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
        ->post(route('lotes.store'), [
            'folio' => 'L-BUSQ-2',
            'id_pedido' => $pedido->id,
            'sucursal' => $sucursalA->id,
            'entregado_en' => '2026-09-01',
            'fecha_caducidad' => '2027-09-01',
            'id_producto' => $producto->id,
            'id_presentacion' => $caja->id,
            'stock' => 10,
        ])
        ->assertRedirect(route('lotes.index', ['sucursal' => $sucursalA->id]));

    $lote = Lote::where('folio', 'L-BUSQ-2')->firstOrFail();

    expect($lote->id_producto)->toBe($producto->id)
        ->and($lote->pedido?->proveedor?->nombre_proveedor)->toBe('Proveedor Busq')
        // Espejo global = suma del inventario: 6 del lote previo + 10 del nuevo.
        ->and($producto->fresh()->stock)->toBe(16);

    $this->assertDatabaseHas('inventario', [
        'id_sucursal' => $sucursalA->id,
        'id_lote' => $lote->id,
        'stock' => 10,
    ]);

    // El proveedor del lote se resuelve vía pedido en el listado.
    $this->withSession(['active_sucursal_id' => $sucursalA->id])
        ->get(route('lotes.index', ['buscar' => 'L-BUSQ-2']))
        ->assertOk()
        ->assertSee('Proveedor Busq');
});

test('el formulario de lote ofrece los productos y sus presentaciones en selects', function () {
    [$sucursalA] = contextoBusquedaInventario($this);

    $this->withSession(['active_sucursal_id' => $sucursalA->id])
        ->get(route('lotes.create'))
        ->assertOk()
        ->assertSee('name="id_producto"', false)
        ->assertSee('name="id_presentacion"', false)
        ->assertSee('Paracetamol 500mg')
        ->assertSee('Ibuprofeno 400mg');
});

test('el alta de lote rechaza una presentacion que no pertenece al producto', function () {
    [$sucursalA] = contextoBusquedaInventario($this);

    $producto = Producto::where('codigo_barras', '7500000000219')->firstOrFail();
    $frasco = PresentacionProducto::create(['presentacion' => 'Frasco', 'descripcion' => '']);

    $this->withSession(['active_sucursal_id' => $sucursalA->id])
        ->post(route('lotes.store'), [
            'folio' => 'L-BUSQ-3',
            'sucursal' => $sucursalA->id,
            'entregado_en' => '2026-09-01',
            'fecha_caducidad' => '2027-09-01',
            'id_producto' => $producto->id,
            'id_presentacion' => $frasco->id,
            'stock' => 5,
        ])
        ->assertSessionHasErrors('id_presentacion');

    $this->assertDatabaseMissing('lotes', ['folio' => 'L-BUSQ-3']);
});
