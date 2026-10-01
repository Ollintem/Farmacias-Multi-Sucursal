<?php

use App\Models\Categoria;
use App\Models\Pedido;
use App\Models\PresentacionProducto;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;

function crearContextoPedido(object $test): array
{
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Pedido',
        'direccion' => 'Av. Pedido 1',
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
        'nombre_proveedor' => 'Proveedor Pedido',
        'direccion' => 'Calle 1',
        'unidad_entrega' => 'Caja',
        'telefono' => '5551234567',
        'correo' => 'proveedor@test.com',
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

    return [$sucursal, $proveedor, $producto, $user];
}

test('el formulario de pedido muestra productos y cantidades', function () {
    [$sucursal, $proveedor, $producto] = crearContextoPedido($this);

    $response = $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('pedidos.create'));

    $response->assertOk()
        ->assertSee('Productos del pedido')
        ->assertSee('Cantidad')
        ->assertSee($producto->nombre_producto);
});

test('el pedido guarda productos con cantidades en detalles_pedido', function () {
    [$sucursal, $proveedor, $producto] = crearContextoPedido($this);

    $response = $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->post(route('pedidos.store'), [
            'id_proveedor' => $proveedor->id,
            'id_sucursal' => $sucursal->id,
            'estado' => 'pendiente',
            'productos' => [
                ['id' => $producto->id, 'cantidad' => 5],
            ],
        ]);

    $response->assertRedirect(route('entradas-de-almacen.index', [
        'sucursal' => $sucursal->id,
        'tipo' => 'pedidos',
    ]));

    $pedido = Pedido::latest('id')->first();

    expect($pedido)->not->toBeNull();
    $this->assertDatabaseHas('detalles_pedido', [
        'pedido' => $pedido->id,
        'producto' => $producto->id,
        'cantidad' => 5,
    ]);
});

test('el pedido rechaza el guardado sin productos', function () {
    [$sucursal, $proveedor] = crearContextoPedido($this);

    $response = $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->post(route('pedidos.store'), [
            'id_proveedor' => $proveedor->id,
            'id_sucursal' => $sucursal->id,
            'estado' => 'pendiente',
            'productos' => [],
        ]);

    $response->assertSessionHasErrors('productos');
    expect(Pedido::count())->toBe(0);
});
