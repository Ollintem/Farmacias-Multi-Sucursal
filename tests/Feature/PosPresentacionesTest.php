<?php

use App\Livewire\PuntoDeVenta;
use App\Models\Caja;
use App\Models\Inventario;
use App\Models\Lote;
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

/**
 * Registra stock real de un producto: lote + inventario en la sucursal + espejo global.
 */
function registrarLotePos(Producto $producto, Sucursal $sucursal, int $unidades, string $caducidad = '2027-12-31'): Lote
{
    $lote = Lote::create([
        'folio' => uniqid('L-POS-'),
        'stock_lote' => $unidades,
        'id_producto' => $producto->id,
        'entregado_en' => now(),
        'fecha_caducidad' => $caducidad,
    ]);

    Inventario::create([
        'id_sucursal' => $sucursal->id,
        'id_lote' => $lote->id,
        'stock' => $unidades,
    ]);

    return $lote;
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
    [$sucursal, , $caja] = crearContextoPos($this);

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

    registrarLotePos($producto, $sucursal, 30);

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
    [$sucursal, , $caja] = crearContextoPos($this);

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

    registrarLotePos($producto, $sucursal, 30);

    $component = Livewire::test(PuntoDeVenta::class);

    $component->call('abrirSelector', $producto->id);
    $component->call('elegirPresentacion', $relacion->id);

    $component->call('abrirCobro');
    $component->set('metodoPago', 'efectivo');
    $component->set('efectivoRecibido', 100);
    $component->call('confirmarVenta');

    $component->assertSet('errorVenta', '');
    $this->assertSame(20, $producto->fresh()->stockTotal());
});

test('no se puede superar el stock en presentaciones del carrito', function () {
    [$sucursal, , $caja] = crearContextoPos($this);

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

    registrarLotePos($producto, $sucursal, 15);

    $component = Livewire::test(PuntoDeVenta::class);

    $component->call('abrirSelector', $producto->id);
    $component->call('elegirPresentacion', $relacion->id);

    // 15 stock / 10 und = máximo 1 presentación
    $component->call('actualizarCantidad', 0, 5);

    $component->assertSet('carrito.0.cantidad', 1);
});

test('el producto con precio unitario se agrega por unidad como antes', function () {
    [$sucursal, , $caja] = crearContextoPos($this);

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

    registrarLotePos($producto, $sucursal, 50);

    $component = Livewire::test(PuntoDeVenta::class);

    $component->call('agregarAlCarrito', $producto->id);

    $component->assertSet('selectorProductoId', null);
    $component->assertSet('carrito.0.tipo_venta', 'unidad');
    $component->assertSet('carrito.0.precio', 2.5);
});

test('el producto divisible abre el selector y ofrece caja o unidad', function () {
    [$sucursal, , $caja] = crearContextoPos($this);

    $producto = Producto::create([
        'codigo_barras' => '7500000000006',
        'nombre_producto' => 'Diclofenaco 50mg',
        'descripcion' => '',
        'stock' => 30,
        'precio' => 2.5,
        'id_presentacion' => $caja->id,
        'es_controlado' => false,
        'es_activo' => true,
    ]);

    $producto->presentacionesPrecio()->create([
        'id_presentacion' => $caja->id,
        'unidades' => 10,
        'precio_presentacion' => 25.0,
    ]);

    registrarLotePos($producto, $sucursal, 30);

    $component = Livewire::test(PuntoDeVenta::class);

    $component->call('agregarAlCarrito', $producto->id);

    $component->assertSet('selectorProductoId', $producto->id);
    $component->assertSet('selectorPrecioUnidad', 2.5);
    $component->assertSet('selectorPresentaciones.0.unidades', 10);
    $component->assertSet('selectorPresentaciones.0.precio', 25.0);
    $component->assertSet('carrito', []);
    $this->assertStringContainsString('elegirUnidad()', $component->html());

    $component->call('elegirUnidad');

    $component->assertSet('selectorProductoId', null);
    $component->assertSet('selectorPrecioUnidad', null);
    $component->assertSet('carrito.0.tipo_venta', 'unidad');
    $component->assertSet('carrito.0.precio', 2.5);
    $component->assertSet('carrito.0.cantidad', 1);
    $component->assertSet('carrito.0.presentacion', '');
});

test('el producto que solo se vende en caja no ofrece la unidad en el selector', function () {
    [, , $caja] = crearContextoPos($this);

    $producto = Producto::create([
        'codigo_barras' => '7500000000007',
        'nombre_producto' => 'Amoxicilina 500mg Caja',
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
    $component->assertSet('selectorPrecioUnidad', null);
    $component->assertSet('carrito', []);
    $this->assertStringNotContainsString('elegirUnidad()', $component->html());
});

test('el POS abre caja al vender unidad y refleja el estado de cajas', function () {
    [$sucursal, , $caja] = crearContextoPos($this);

    $producto = Producto::create([
        'codigo_barras' => '7500000000008',
        'nombre_producto' => 'Naproxeno 200mg',
        'descripcion' => '',
        'stock' => 10,
        'precio' => 2.5,
        'id_presentacion' => $caja->id,
        'es_controlado' => false,
        'es_activo' => true,
    ]);

    $producto->presentacionesPrecio()->create([
        'id_presentacion' => $caja->id,
        'unidades' => 10,
        'precio_presentacion' => 25.0,
    ]);

    registrarLotePos($producto, $sucursal, 10);

    $component = Livewire::test(PuntoDeVenta::class);

    $component->call('agregarAlCarrito', $producto->id);

    $component->assertSet('selectorEstadoCajas.selladas', 1)
        ->assertSet('selectorEstadoCajas.sueltas', 0);
    $this->assertStringContainsString('Al vender 1 ud se abrirá una caja', $component->html());
    $this->assertStringNotContainsString('Stock insuficiente', $component->html());

    $component->call('elegirUnidad');
    $component->call('abrirCobro');
    $component->set('metodoPago', 'efectivo');
    $component->set('efectivoRecibido', 100);
    $component->call('confirmarVenta');

    $component->assertSet('errorVenta', '');
    $this->assertSame(9, $producto->fresh()->stockTotal());

    $component->call('abrirSelector', $producto->id);

    $component->assertSet('selectorEstadoCajas.selladas', 0)
        ->assertSet('selectorEstadoCajas.sueltas', 9);
    $this->assertStringNotContainsString('Al vender 1 ud se abrirá una caja', $component->html());
});

test('la presentacion que no cabe en el stock aparece deshabilitada en el selector', function () {
    [$sucursal, , $caja] = crearContextoPos($this);

    $producto = Producto::create([
        'codigo_barras' => '7500000000009',
        'nombre_producto' => 'Amoxicilina 250mg',
        'descripcion' => '',
        'stock' => 9,
        'precio' => null,
        'id_presentacion' => $caja->id,
        'es_controlado' => false,
        'es_activo' => true,
    ]);

    $producto->presentacionesPrecio()->create([
        'id_presentacion' => $caja->id,
        'unidades' => 10,
        'precio_presentacion' => 80.0,
    ]);

    registrarLotePos($producto, $sucursal, 9);

    $component = Livewire::test(PuntoDeVenta::class);

    $component->call('agregarAlCarrito', $producto->id);

    $component->assertSet('selectorPresentaciones.0.maximo', 0);
    $this->assertStringContainsString('Stock insuficiente', $component->html());
});
