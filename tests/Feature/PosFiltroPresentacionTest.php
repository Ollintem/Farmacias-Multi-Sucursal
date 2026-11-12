<?php

use App\Livewire\PuntoDeVenta;
use App\Models\CorteCaja;
use App\Models\Inventario;
use App\Models\Lote;
use App\Models\PresentacionProducto;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;
use Livewire\Livewire;

function contextoFiltroPresentacion(object $test): array
{
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Filtro Presentacion',
        'direccion' => 'Av. Filtro 100',
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

    $caja = PresentacionProducto::create(['presentacion' => 'Caja', 'descripcion' => '']);
    $blister = PresentacionProducto::create(['presentacion' => 'Blister', 'descripcion' => '']);

    $test->actingAs($user);
    session(['active_sucursal_id' => $sucursal->id]);

    CorteCaja::create([
        'id_sucursal' => $sucursal->id,
        'id_usuario' => $user->id,
        'caja' => 'Caja 1',
        'turno' => 'Matutino',
        'efectivo_inicial' => 0,
        'efectivo_declarado' => 0,
        'efectivo_esperado' => 0,
        'diferencia' => 0,
        'fecha_inicio' => now(),
        'estado' => 'abierto',
    ]);

    return [$sucursal, $caja, $blister];
}

/**
 * Crea un producto con una presentación y stock real en la sucursal activa.
 */
function productoFiltroPresentacion(
    Sucursal $sucursal,
    PresentacionProducto $presentacion,
    string $nombre,
    string $codigoBarras,
    bool $esControlado = false,
): Producto {
    $producto = Producto::create([
        'codigo_barras' => $codigoBarras,
        'nombre_producto' => $nombre,
        'descripcion' => '',
        'es_controlado' => $esControlado,
        'es_activo' => true,
    ]);

    $producto->presentacionesPrecio()->create([
        'id_presentacion' => $presentacion->id,
        'unidades' => 10,
        'es_principal' => true,
        'precio_presentacion' => 25.0,
    ]);

    $lote = Lote::create([
        'folio' => uniqid('L-FILTRO-'),
        'stock_lote' => 20,
        'id_producto' => $producto->id,
        'entregado_en' => now(),
        'fecha_caducidad' => '2027-12-31',
    ]);

    Inventario::create([
        'id_sucursal' => $sucursal->id,
        'id_lote' => $lote->id,
        'stock_individual' => 20,
    ]);

    return $producto;
}

test('sin filtro el catalogo muestra todos los productos con inventario en la sucursal', function () {
    [$sucursal, $caja, $blister] = contextoFiltroPresentacion($this);
    productoFiltroPresentacion($sucursal, $caja, 'Amoxicilina 500mg', '7509000000001');
    productoFiltroPresentacion($sucursal, $blister, 'Ibuprofeno 400mg', '7509000000002');

    $component = Livewire::test(PuntoDeVenta::class);

    $component->assertSet('filtroPresentacion', '');
    $component->assertSet('busqueda', '');
    $this->assertStringContainsString('2 productos', $component->html());
    $this->assertStringContainsString('Amoxicilina 500mg', $component->html());
    $this->assertStringContainsString('Ibuprofeno 400mg', $component->html());
});

test('filtrar por presentacion muestra unicamente los productos que la ofrecen', function () {
    [$sucursal, $caja, $blister] = contextoFiltroPresentacion($this);
    productoFiltroPresentacion($sucursal, $caja, 'Amoxicilina 500mg', '7509000000001');
    productoFiltroPresentacion($sucursal, $blister, 'Ibuprofeno 400mg', '7509000000002');

    $component = Livewire::test(PuntoDeVenta::class);

    $component->set('filtroPresentacion', (string) $blister->id);

    $this->assertStringContainsString('Ibuprofeno 400mg', $component->html());
    $this->assertStringNotContainsString('Amoxicilina 500mg', $component->html());
    $component->assertSet('filtroPresentacion', (string) $blister->id);
});

test('el filtro de presentacion se combina con la busqueda sin limpiarse entre si', function () {
    [$sucursal, $caja, $blister] = contextoFiltroPresentacion($this);
    productoFiltroPresentacion($sucursal, $caja, 'Amoxicilina 500mg', '7509000000001');
    productoFiltroPresentacion($sucursal, $blister, 'Ibuprofeno 400mg', '7509000000002');

    $component = Livewire::test(PuntoDeVenta::class);

    $component->set('busqueda', 'ibuprofeno')
        ->set('filtroPresentacion', (string) $blister->id);

    $this->assertStringContainsString('Ibuprofeno 400mg', $component->html());
    $component->assertSet('busqueda', 'ibuprofeno');
    $component->assertSet('filtroPresentacion', (string) $blister->id);

    $component->set('filtroPresentacion', (string) $caja->id);

    $this->assertStringNotContainsString('Ibuprofeno 400mg', $component->html());
    $this->assertStringContainsString('No se encontraron productos', $component->html());
    $component->assertSet('busqueda', 'ibuprofeno');

    $component->set('busqueda', '');

    $component->assertSet('filtroPresentacion', (string) $caja->id);
    $this->assertStringContainsString('Amoxicilina 500mg', $component->html());
});

test('una presentacion sin productos muestra el estado vacio sin errores', function () {
    [$sucursal, $caja] = contextoFiltroPresentacion($this);
    productoFiltroPresentacion($sucursal, $caja, 'Amoxicilina 500mg', '7509000000001');

    $vacia = PresentacionProducto::create(['presentacion' => 'Spray', 'descripcion' => '']);

    $component = Livewire::test(PuntoDeVenta::class);

    $component->set('filtroPresentacion', (string) $vacia->id);

    $this->assertStringContainsString('No se encontraron productos', $component->html());
    $this->assertStringNotContainsString('Amoxicilina 500mg', $component->html());
});

test('el selector de presentaciones refleja altas y bajas en la base de datos', function () {
    contextoFiltroPresentacion($this);

    $component = Livewire::test(PuntoDeVenta::class);

    $this->assertStringContainsString('Blister', $component->html());

    PresentacionProducto::create(['presentacion' => 'Spray', 'descripcion' => '']);

    $component->call('$refresh');

    $this->assertStringContainsString('Spray', $component->html());

    PresentacionProducto::where('presentacion', 'Spray')->delete();

    $component->call('$refresh');

    $this->assertStringNotContainsString('Spray', $component->html());
});

test('con una presentacion filtrada el producto se agrega directo en ella sin abrir el selector', function () {
    [$sucursal, $caja, $blister] = contextoFiltroPresentacion($this);
    $producto = productoFiltroPresentacion($sucursal, $caja, 'Amoxicilina 500mg', '7509000000001');

    $relacionBlister = $producto->presentacionesPrecio()->create([
        'id_presentacion' => $blister->id,
        'unidades' => 5,
        'es_principal' => false,
        'precio_presentacion' => 12.5,
    ]);

    $component = Livewire::test(PuntoDeVenta::class);

    $component->set('filtroPresentacion', (string) $blister->id)
        ->call('agregarAlCarrito', $producto->id);

    $component->assertSet('selectorProductoId', null);
    $component->assertSet('mostrandoReceta', false);
    $component->assertCount('carrito', 1);
    $component->assertSet('carrito.0.producto_id', $producto->id);
    $component->assertSet('carrito.0.tipo_venta', 'presentacion');
    $component->assertSet('carrito.0.presentacion', 'Blister');
    $component->assertSet('carrito.0.presentacion_id', $relacionBlister->id);
});

test('con el filtro en unidad el producto se agrega como venta unitaria', function () {
    [$sucursal, $caja] = contextoFiltroPresentacion($this);
    $unidad = PresentacionProducto::create(['presentacion' => 'Unidad', 'descripcion' => '']);
    $producto = productoFiltroPresentacion($sucursal, $caja, 'Amoxicilina 500mg', '7509000000001');

    $producto->presentacionesPrecio()->create([
        'id_presentacion' => $unidad->id,
        'unidades' => 1,
        'es_principal' => false,
        'precio_presentacion' => 3.5,
    ]);

    $component = Livewire::test(PuntoDeVenta::class);

    $component->set('filtroPresentacion', (string) $unidad->id)
        ->call('agregarAlCarrito', $producto->id);

    $component->assertSet('selectorProductoId', null);
    $component->assertCount('carrito', 1);
    $component->assertSet('carrito.0.producto_id', $producto->id);
    $component->assertSet('carrito.0.tipo_venta', 'unidad');
});

test('producto controlado con presentacion filtrada pide la receta con esa presentacion', function () {
    [$sucursal, $caja, $blister] = contextoFiltroPresentacion($this);
    $producto = productoFiltroPresentacion($sucursal, $caja, 'Clonazepam 2mg', '7509000000001', esControlado: true);

    $relacionBlister = $producto->presentacionesPrecio()->create([
        'id_presentacion' => $blister->id,
        'unidades' => 5,
        'es_principal' => false,
        'precio_presentacion' => 12.5,
    ]);

    $component = Livewire::test(PuntoDeVenta::class);

    $component->set('filtroPresentacion', (string) $blister->id)
        ->call('agregarAlCarrito', $producto->id);

    $component->assertSet('selectorProductoId', null);
    $component->assertSet('mostrandoReceta', true);
    $component->assertSet('recetaProductoId', $producto->id);
    $component->assertSet('recetaPresentacionId', $relacionBlister->id);
    $component->assertCount('carrito', 0);
});

test('si el producto no ofrece la presentacion filtrada se abre el selector', function () {
    [$sucursal, $caja, $blister] = contextoFiltroPresentacion($this);
    $producto = productoFiltroPresentacion($sucursal, $caja, 'Amoxicilina 500mg', '7509000000001');

    $component = Livewire::test(PuntoDeVenta::class);

    $component->set('filtroPresentacion', (string) $blister->id)
        ->call('agregarAlCarrito', $producto->id);

    $component->assertSet('selectorProductoId', $producto->id);
    $component->assertCount('carrito', 0);
});

test('el badge de stock muestra la presentacion filtrada truncando el decimal', function () {
    [$sucursal, $caja] = contextoFiltroPresentacion($this);
    $producto = productoFiltroPresentacion($sucursal, $caja, 'Amoxicilina 500mg', '7509000000001');

    $producto->presentacionesPrecio()
        ->where('id_presentacion', $caja->id)
        ->update(['unidades' => 8]);

    $component = Livewire::test(PuntoDeVenta::class);

    $this->assertStringContainsString('20uds disp.', $component->html());

    $component->set('filtroPresentacion', (string) $caja->id);

    // 20 unidades ÷ 8 por presentación = 2.5 → se muestra 2: truncado,
    // nunca redondeado hacia arriba.
    $this->assertStringContainsString('2uds disp.', $component->html());
    $this->assertStringNotContainsString('20uds disp.', $component->html());
});

test('la tarjeta muestra el precio de la presentacion filtrada y su nombre en el chip', function () {
    [$sucursal, $caja, $blister] = contextoFiltroPresentacion($this);
    $producto = productoFiltroPresentacion($sucursal, $caja, 'Amoxicilina 500mg', '7509000000001');

    $producto->presentacionesPrecio()->create([
        'id_presentacion' => $blister->id,
        'unidades' => 5,
        'es_principal' => false,
        'precio_presentacion' => 12.5,
    ]);

    $component = Livewire::test(PuntoDeVenta::class);

    // Sin filtro: precio más barato entre presentaciones y chip de la principal.
    $this->assertStringContainsString('Desde $12.50', $component->html());
    $this->assertStringContainsString('Caja · 10 uds', $component->html());

    // Con filtro Blister: precio exacto de esa presentación y su nombre real.
    $component->set('filtroPresentacion', (string) $blister->id);

    $html = $component->html();
    $this->assertStringContainsString('$12.50', $html);
    $this->assertStringNotContainsString('Desde', $html);
    $this->assertStringContainsString('Blister · 5 uds', $html);
    $this->assertStringNotContainsString('Caja · 10 uds', $html);

    // Con filtro Caja: el precio de la principal, sin «Desde».
    $component->set('filtroPresentacion', (string) $caja->id);

    $html = $component->html();
    $this->assertStringContainsString('$25.00', $html);
    $this->assertStringNotContainsString('Desde', $html);
    $this->assertStringContainsString('Caja · 10 uds', $html);
});

test('con una sola presentacion sin filtro se muestra su precio sin desde', function () {
    [$sucursal, $caja] = contextoFiltroPresentacion($this);
    productoFiltroPresentacion($sucursal, $caja, 'Amoxicilina 500mg', '7509000000001');

    $component = Livewire::test(PuntoDeVenta::class);

    $html = $component->html();
    $this->assertStringContainsString('$25.00', $html);
    $this->assertStringNotContainsString('Desde', $html);
    $this->assertStringContainsString('Caja · 10 uds', $html);
});
