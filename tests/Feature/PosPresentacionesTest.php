<?php

use App\Livewire\PuntoDeVenta;
use App\Models\Caja;
use App\Models\PresentacionProducto;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;
use Livewire\Livewire;

function crearContextoPos(object $test): array
{
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal POS',
        'direccion' => 'Av. POS 100',
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

    Caja::firstOrCreate(['id_sucursal' => $sucursal->id]);

    $caja = PresentacionProducto::create(['presentacion' => 'Caja', 'descripcion' => '']);

    // Sesión y auth ANTES de crear productos para que el trait los vincule a la sucursal
    $test->actingAs($user);
    session(['active_sucursal_id' => $sucursal->id]);

    return [$sucursal, $user, $caja];
}

test('un producto sin precio unitario abre el selector de presentaciones', function () {
    [, , $caja] = crearContextoPos($this);

    $producto = Producto::create([
        'codigo_barras' => '7500000000001',
        'nombre_producto' => 'Amoxicilina 500mg',
        'descripcion' => '',
        'stock' => 30,
        'precio' => null,
        'id_presentacion' => $caja->id,
        'es_controlado' => false,
        'es_activo' => true,
    ]);

    $producto->presentacionesPrecio()->create([
        'id_presentacion' => $caja->id,
        'unidades' => 10,
        'precio_presentacion' => 85.0,
    ]);

    $component = Livewire::test(PuntoDeVenta::class);

    $component->call('agregarAlCarrito', $producto->id);

    $component->assertSet('selectorProductoId', $producto->id);
    $component->assertSet('selectorNombreProducto', 'Amoxicilina 500mg');
    $component->assertSet('selectorPresentaciones.0.unidades', 10);
    $component->assertSet('selectorPresentaciones.0.precio', 85.0);
    $component->assertSet('carrito', []);
});

test('elegir presentacion agrega el item al carrito con su precio', function () {
    [, , $caja] = crearContextoPos($this);

    $producto = Producto::create([
        'codigo_barras' => '7500000000002',
        'nombre_producto' => 'Amoxicilina 500mg',
        'descripcion' => '',
        'stock' => 30,
        'precio' => null,
        'id_presentacion' => $caja->id,
        'es_controlado' => false,
        'es_activo' => true,
    ]);

    $relacion = $producto->presentacionesPrecio()->create([
        'id_presentacion' => $caja->id,
        'unidades' => 10,
        'precio_presentacion' => 85.0,
    ]);

    $component = Livewire::test(PuntoDeVenta::class);

    $component->call('abrirSelector', $producto->id);
    $component->call('elegirPresentacion', $relacion->id);

    $component->assertSet('selectorProductoId', null);
    $component->assertSet('carrito.0.tipo_venta', 'presentacion');
    $component->assertSet('carrito.0.precio', 85.0);
    $component->assertSet('carrito.0.unidades', 10);
    $component->assertSet('carrito.0.cantidad', 1);
    $component->assertSet('carrito.0.presentacion_id', $relacion->id);
});

test('confirmar venta descuenta stock por unidades al vender presentaciones', function () {
    [, , $caja] = crearContextoPos($this);

    $producto = Producto::create([
        'codigo_barras' => '7500000000003',
        'nombre_producto' => 'Ibuprofeno 400mg',
        'descripcion' => '',
        'stock' => 30,
        'precio' => null,
        'id_presentacion' => $caja->id,
        'es_controlado' => false,
        'es_activo' => true,
    ]);

    $relacion = $producto->presentacionesPrecio()->create([
        'id_presentacion' => $caja->id,
        'unidades' => 10,
        'precio_presentacion' => 85.0,
    ]);

    $component = Livewire::test(PuntoDeVenta::class);

    $component->call('abrirSelector', $producto->id);
    $component->call('elegirPresentacion', $relacion->id);

    $component->call('abrirCobro');
    $component->set('metodoPago', 'efectivo');
    $component->set('efectivoRecibido', 100);
    $component->call('confirmarVenta');

    $component->assertSet('errorVenta', '');
    $this->assertSame(20, $producto->fresh()->stock);
});

test('no se puede superar el stock en presentaciones del carrito', function () {
    [, , $caja] = crearContextoPos($this);

    $producto = Producto::create([
        'codigo_barras' => '7500000000004',
        'nombre_producto' => 'Naproxeno 250mg',
        'descripcion' => '',
        'stock' => 15,
        'precio' => null,
        'id_presentacion' => $caja->id,
        'es_controlado' => false,
        'es_activo' => true,
    ]);

    $relacion = $producto->presentacionesPrecio()->create([
        'id_presentacion' => $caja->id,
        'unidades' => 10,
        'precio_presentacion' => 70.0,
    ]);

    $component = Livewire::test(PuntoDeVenta::class);

    $component->call('abrirSelector', $producto->id);
    $component->call('elegirPresentacion', $relacion->id);

    // 15 stock / 10 und = máximo 1 presentación
    $component->call('actualizarCantidad', 0, 5);

    $component->assertSet('carrito.0.cantidad', 1);
});

test('el producto con precio unitario se agrega por unidad como antes', function () {
    [, , $caja] = crearContextoPos($this);

    $producto = Producto::create([
        'codigo_barras' => '7500000000005',
        'nombre_producto' => 'Paracetamol 500mg',
        'descripcion' => '',
        'stock' => 50,
        'precio' => 2.5,
        'id_presentacion' => $caja->id,
        'es_controlado' => false,
        'es_activo' => true,
    ]);

    $component = Livewire::test(PuntoDeVenta::class);

    $component->call('agregarAlCarrito', $producto->id);

    $component->assertSet('selectorProductoId', null);
    $component->assertSet('carrito.0.tipo_venta', 'unidad');
    $component->assertSet('carrito.0.precio', 2.5);
});
