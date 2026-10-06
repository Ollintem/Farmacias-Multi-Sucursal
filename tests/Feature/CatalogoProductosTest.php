<?php

use App\Models\Caja;
use App\Models\Categoria;
use App\Models\Inventario;
use App\Models\Lote;
use App\Models\Pago;
use App\Models\PresentacionProducto;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Venta;

function crearContextoCatalogo(object $test): array
{
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Catalogo',
        'direccion' => 'Av. Catalogo 1',
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

    $categoria = Categoria::create(['nombre' => 'Analgesicos']);
    $caja = PresentacionProducto::create(['presentacion' => 'Caja', 'descripcion' => '']);
    $blister = PresentacionProducto::create(['presentacion' => 'Blister', 'descripcion' => '']);

    // Sesion y auth ANTES de crear productos para que los vincule a la sucursal.
    $test->actingAs($user);
    session(['active_sucursal_id' => $sucursal->id]);

    return [$sucursal, $categoria, $caja, $blister];
}

function crearProductoCatalogo(Categoria $categoria, PresentacionProducto $caja, array $atributos = []): Producto
{
    $producto = Producto::create(array_merge([
        'codigo_barras' => '7501112223345',
        'nombre_producto' => 'Paracetamol 500mg',
        'descripcion' => '',
        'stock' => 0,
        'precio' => null,
        'id_presentacion' => $caja->id,
        'id_categoria' => $categoria->id,
        'es_controlado' => false,
        'es_activo' => true,
    ], $atributos));

    $producto->presentacionesPrecio()->create([
        'id_presentacion' => $caja->id,
        'unidades' => 10,
        'precio_presentacion' => 22.5,
    ]);

    return $producto;
}

test('la pestana productos muestra el catalogo con sus columnas', function () {
    [$sucursal, $categoria, $caja] = crearContextoCatalogo($this);
    $producto = crearProductoCatalogo($categoria, $caja, ['es_controlado' => true]);

    $response = $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('inventario.productos'));

    $response->assertOk()
        ->assertSee('Paracetamol 500mg')
        ->assertSee('7501112223345')
        ->assertSee('Analgesicos')
        ->assertSee('Caja')
        ->assertSee('$22.50')
        ->assertSee('Sí')
        ->assertSee('Activo')
        ->assertSee('+ Agregar producto nuevo')
        ->assertSee(route('inventario.edit', $producto))
        ->assertSee('value="PATCH"', false)
        ->assertSee('data-modo="eliminar"', false);
});

test('el boton de agregar producto nuevo se mueve a la pestana productos', function () {
    [$sucursal] = crearContextoCatalogo($this);

    $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('inventario.productos'))
        ->assertOk()
        ->assertSee('+ Agregar producto nuevo');

    $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('inventario.stock'))
        ->assertOk()
        ->assertDontSee('+ Agregar producto nuevo');
});

test('los productos inactivos no aparecen en productos y stock ni en punto de venta', function () {
    [$sucursal, $categoria, $caja] = crearContextoCatalogo($this);

    crearProductoCatalogo($categoria, $caja, [
        'codigo_barras' => '7500000000011',
        'nombre_producto' => 'Ibuprofeno 400mg',
    ]);
    crearProductoCatalogo($categoria, $caja, [
        'codigo_barras' => '7500000000028',
        'nombre_producto' => 'Naproxeno 250mg',
        'es_activo' => false,
    ]);

    $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('inventario.stock'))
        ->assertOk()
        ->assertSee('Ibuprofeno 400mg')
        ->assertDontSee('Naproxeno 250mg');

    $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('inventario.productos'))
        ->assertOk()
        ->assertSee('Naproxeno 250mg');

    $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('punto-venta.index'))
        ->assertOk()
        ->assertSee('Ibuprofeno 400mg')
        ->assertDontSee('Naproxeno 250mg');
});

test('se puede desactivar un producto que tiene stock sin tocar el inventario', function () {
    [$sucursal, $categoria, $caja] = crearContextoCatalogo($this);

    $conStockGlobal = crearProductoCatalogo($categoria, $caja, [
        'codigo_barras' => '7500000000035',
        'nombre_producto' => 'Stock global',
        'stock' => 12,
    ]);

    $conStockSucursal = crearProductoCatalogo($categoria, $caja, [
        'codigo_barras' => '7500000000042',
        'nombre_producto' => 'Stock sucursal',
    ]);

    $loteSucursal = Lote::create([
        'folio' => 'L-STOCK-1',
        'stock_lote' => 6,
        'id_producto' => $conStockSucursal->id,
        'fecha_caducidad' => '2027-01-01',
    ]);

    Inventario::create([
        'id_sucursal' => $sucursal->id,
        'id_lote' => $loteSucursal->id,
        'stock' => 6,
    ]);

    foreach ([$conStockGlobal, $conStockSucursal] as $producto) {
        $this->withSession(['active_sucursal_id' => $sucursal->id])
            ->from(route('inventario.productos'))
            ->patch(route('inventario.estado', $producto), ['es_activo' => 0])
            ->assertSessionHas('success');
    }

    $this->assertDatabaseHas('productos', ['id' => $conStockGlobal->id, 'es_activo' => 0]);
    $this->assertDatabaseHas('inventario', ['id_lote' => $loteSucursal->id, 'stock' => 6]);
    $this->assertDatabaseHas('lotes', ['id' => $loteSucursal->id, 'stock_lote' => 6]);
});

test('se puede desactivar un producto con lotes y desaparece de pos conservando el historial', function () {
    [$sucursal, $categoria, $caja] = crearContextoCatalogo($this);

    $producto = crearProductoCatalogo($categoria, $caja, [
        'codigo_barras' => '7500000000059',
        'nombre_producto' => 'Con lote',
    ]);

    $lote = Lote::create([
        'folio' => 'L-0001',
        'stock_lote' => 5,
        'id_producto' => $producto->id,
        'fecha_caducidad' => '2027-01-01',
    ]);

    $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->from(route('inventario.productos'))
        ->patch(route('inventario.estado', $producto), ['es_activo' => 0])
        ->assertSessionHas('success');

    $this->assertDatabaseHas('productos', ['id' => $producto->id, 'es_activo' => 0]);
    $this->assertDatabaseHas('lotes', ['id' => $lote->id, 'stock_lote' => 5]);

    // Desaparece de Punto de Venta...
    // (un GET previo consume el toast del cambio de estado para que no
    // contamine la revisión de la página.)
    $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('inventario.productos'))
        ->assertOk();

    $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('punto-venta.index'))
        ->assertOk()
        ->assertDontSee('Con lote');

    // ...pero su lote sigue listado en Lotes y caducidades.
    $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('lotes.index'))
        ->assertOk()
        ->assertSee('L-0001');

    // Se puede reactivar desde el mismo botón.
    $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->from(route('inventario.productos'))
        ->patch(route('inventario.estado', $producto), ['es_activo' => 1])
        ->assertSessionHas('success');

    $this->assertDatabaseHas('productos', ['id' => $producto->id, 'es_activo' => 1]);
});

test('se puede desactivar y reactivar un producto sin stock ni lotes', function () {
    [$sucursal, $categoria, $caja] = crearContextoCatalogo($this);

    $producto = crearProductoCatalogo($categoria, $caja, [
        'codigo_barras' => '7500000000066',
        'nombre_producto' => 'Sin relacion',
    ]);

    $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->from(route('inventario.productos'))
        ->patch(route('inventario.estado', $producto), ['es_activo' => 0])
        ->assertSessionHas('success');

    $this->assertDatabaseHas('productos', ['id' => $producto->id, 'es_activo' => 0]);

    $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->from(route('inventario.productos'))
        ->patch(route('inventario.estado', $producto), ['es_activo' => 1])
        ->assertSessionHas('success');

    $this->assertDatabaseHas('productos', ['id' => $producto->id, 'es_activo' => 1]);
});

test('no se puede eliminar un producto con ventas y su historial queda intacto', function () {
    [$sucursal, $categoria, $caja] = crearContextoCatalogo($this);

    $producto = crearProductoCatalogo($categoria, $caja, [
        'codigo_barras' => '7500000000073',
        'nombre_producto' => 'Con ventas',
    ]);

    $cajaVenta = Caja::firstOrCreate(['id_sucursal' => $sucursal->id]);
    $pago = Pago::create([
        'monto' => 22.5,
        'metodo' => 'Efectivo',
        'estado' => 'Completada',
        'referencia' => 'REF-1',
    ]);

    $venta = Venta::create([
        'id_caja' => $cajaVenta->id,
        'folio' => 'V-0001',
        'descuento' => 0,
        'total' => 22.5,
        'id_pago' => $pago->id,
        'estado' => 'Completada',
    ]);

    $venta->productos()->attach($producto->id, ['cantidad' => 1, 'precio_unidad' => 22.5]);

    $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->from(route('inventario.productos'))
        ->delete(route('inventario.destroy', $producto))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('productos', ['id' => $producto->id]);
    $this->assertDatabaseHas('producto_venta', ['venta' => $venta->id, 'producto' => $producto->id]);
});

test('no se puede eliminar un producto con lotes o con stock', function () {
    [$sucursal, $categoria, $caja] = crearContextoCatalogo($this);

    $conLote = crearProductoCatalogo($categoria, $caja, [
        'codigo_barras' => '7500000000080',
        'nombre_producto' => 'Producto con lote',
    ]);

    Lote::create([
        'folio' => 'L-0002',
        'stock_lote' => 3,
        'id_producto' => $conLote->id,
        'fecha_caducidad' => '2027-06-01',
    ]);

    $conStock = crearProductoCatalogo($categoria, $caja, [
        'codigo_barras' => '7500000000097',
        'nombre_producto' => 'Producto con stock',
    ]);

    $loteConStock = Lote::create([
        'folio' => 'L-0003',
        'stock_lote' => 4,
        'id_producto' => $conStock->id,
        'fecha_caducidad' => '2027-06-01',
    ]);

    Inventario::create([
        'id_sucursal' => $sucursal->id,
        'id_lote' => $loteConStock->id,
        'stock' => 4,
    ]);

    foreach ([$conLote, $conStock] as $producto) {
        $this->withSession(['active_sucursal_id' => $sucursal->id])
            ->from(route('inventario.productos'))
            ->delete(route('inventario.destroy', $producto))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('productos', ['id' => $producto->id]);
    }
});

test('se elimina del catalogo un producto sin relaciones', function () {
    [, $categoria, $caja] = crearContextoCatalogo($this);

    $producto = crearProductoCatalogo($categoria, $caja, [
        'codigo_barras' => '7500000000103',
        'nombre_producto' => 'Producto libre',
    ]);

    $this->from(route('inventario.productos'))
        ->delete(route('inventario.destroy', $producto))
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('productos', ['id' => $producto->id]);
    $this->assertDatabaseMissing('presentacion_producto', ['producto' => $producto->id]);
});

test('la lista desplegable muestra las presentaciones distintas a la caja', function () {
    [$sucursal, $categoria, $caja, $blister] = crearContextoCatalogo($this);

    $producto = crearProductoCatalogo($categoria, $caja);

    $producto->presentacionesPrecio()->create([
        'id_presentacion' => $blister->id,
        'unidades' => 5,
        'precio_presentacion' => 3.5,
    ]);

    $response = $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('inventario.productos'));

    $response->assertOk()
        ->assertSee('+ 1 presentación')
        ->assertSee('Presentaciones y precios')
        ->assertSee('presentaciones-'.$producto->id, false)
        ->assertSee('5 ud.')
        ->assertSee('$3.50')
        ->assertSee('$22.50')
        ->assertDontSee('10 ud.')
        ->assertDontSee('principal')
        ->assertDontSee('Unitario');
});

test('la pestana productos muestra el precio unitario como registro primero de la lista', function () {
    [$sucursal, $categoria, $caja] = crearContextoCatalogo($this);

    $sinUnidad = crearProductoCatalogo($categoria, $caja, [
        'codigo_barras' => '7500000000110',
        'nombre_producto' => 'Solo por presentacion',
    ]);

    $conUnidad = crearProductoCatalogo($categoria, $caja, [
        'codigo_barras' => '7500000000127',
        'nombre_producto' => 'Tambien por unidad',
        'precio' => 1.25,
    ]);

    $response = $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('inventario.productos'));

    $response->assertOk()
        ->assertSee('Unitario')
        ->assertSee('1 ud.')
        ->assertSee('$1.25')
        ->assertSee('+ 1 presentación');

    $cuerpo = $response->getContent();
    $posicionSolo = strpos($cuerpo, 'Solo por presentacion');
    $posicionUnidad = strpos($cuerpo, 'Tambien por unidad');

    expect($posicionSolo)->not->toBeFalse()
        ->and($posicionUnidad)->not->toBeFalse()
        ->and($posicionSolo)->toBeLessThan($posicionUnidad)
        ->and(substr_count($cuerpo, 'Unitario'))->toBe(1);

    $bloqueSolo = substr($cuerpo, $posicionSolo, $posicionUnidad - $posicionSolo);

    expect(str_contains($bloqueSolo, 'Unitario'))->toBeFalse();
});

test('el contador de presentaciones incluye la unitaria cuando esta habilitada', function () {
    [$sucursal, $categoria, $caja, $blister] = crearContextoCatalogo($this);
    $par = PresentacionProducto::create(['presentacion' => 'Par', 'descripcion' => '']);

    $producto = crearProductoCatalogo($categoria, $caja, ['precio' => 1.25]);

    $producto->presentacionesPrecio()->create([
        'id_presentacion' => $blister->id,
        'unidades' => 5,
        'precio_presentacion' => 3.5,
    ]);

    $producto->presentacionesPrecio()->create([
        'id_presentacion' => $par->id,
        'unidades' => 2,
        'precio_presentacion' => 4,
    ]);

    $response = $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('inventario.productos'));

    $response->assertOk()
        ->assertSee('+ 3 presentaciones')
        ->assertSee('Unitario')
        ->assertSee('Blister')
        ->assertSee('$1.25')
        ->assertSee('$3.50')
        ->assertSee('$4.00')
        ->assertDontSee('10 ud.');
});

test('la lista desplegable cuenta solo las presentaciones que no son caja', function () {
    [$sucursal, $categoria, $caja, $blister] = crearContextoCatalogo($this);
    $tubo = PresentacionProducto::create(['presentacion' => 'Tubo', 'descripcion' => '']);

    $producto = crearProductoCatalogo($categoria, $caja);

    $producto->presentacionesPrecio()->create([
        'id_presentacion' => $blister->id,
        'unidades' => 5,
        'precio_presentacion' => 3.5,
    ]);

    $producto->presentacionesPrecio()->create([
        'id_presentacion' => $tubo->id,
        'unidades' => 2,
        'precio_presentacion' => 8,
    ]);

    $response = $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('inventario.productos'));

    $response->assertOk()
        ->assertSee('+ 2 presentaciones')
        ->assertSee('5 ud.')
        ->assertSee('2 ud.')
        ->assertSee('$3.50')
        ->assertSee('$8.00')
        ->assertDontSee('10 ud.')
        ->assertDontSee('principal');
});

test('sin presentaciones adicionales ni precio unitario no se muestra el desplegable', function () {
    [$sucursal, $categoria, $caja] = crearContextoCatalogo($this);

    $producto = crearProductoCatalogo($categoria, $caja, [
        'codigo_barras' => '7500000000134',
        'nombre_producto' => 'Solo caja',
    ]);

    $response = $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('inventario.productos'));

    $response->assertOk()
        ->assertSee('Caja')
        ->assertSee('$22.50')
        ->assertDontSee('presentaciones-'.$producto->id, false)
        ->assertDontSee('presentación');
});

test('la edicion actualiza los datos y agrega presentaciones', function () {
    [$sucursal, $categoria, $caja, $blister] = crearContextoCatalogo($this);

    $producto = crearProductoCatalogo($categoria, $caja);

    $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('inventario.edit', $producto))
        ->assertOk()
        ->assertSee('Editar producto')
        ->assertSee('Guardar cambios')
        ->assertSee('Paracetamol 500mg');

    $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->from(route('inventario.productos'))
        ->put(route('inventario.update', $producto), [
            'codigo_barras' => '7509998887776',
            'nombre_producto' => 'Paracetamol 1000mg',
            'id_categoria' => $categoria->id,
            'descripcion' => 'Fuerza doble',
            'vender_por_unidad' => '1',
            'precio' => 3.5,
            'es_controlado' => '1',
            'presentaciones' => [
                ['id_presentacion' => $caja->id, 'unidades' => 20, 'precio' => 45],
                ['id_presentacion' => $blister->id, 'unidades' => 7, 'precio' => 4],
            ],
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('productos', [
        'id' => $producto->id,
        'codigo_barras' => '7509998887776',
        'nombre_producto' => 'Paracetamol 1000mg',
        'id_categoria' => $categoria->id,
        'descripcion' => 'Fuerza doble',
        'precio' => 3.5,
        'es_controlado' => true,
        'id_presentacion' => $caja->id,
    ]);

    $this->assertDatabaseHas('presentacion_producto', [
        'producto' => $producto->id,
        'id_presentacion' => $caja->id,
        'unidades' => 20,
        'precio_presentacion' => 45,
    ]);

    $this->assertDatabaseHas('presentacion_producto', [
        'producto' => $producto->id,
        'id_presentacion' => $blister->id,
        'unidades' => 7,
        'precio_presentacion' => 4,
    ]);
});

test('los botones de eliminar y desactivar abren una ventana emergente', function () {
    [$sucursal, $categoria, $caja] = crearContextoCatalogo($this);

    crearProductoCatalogo($categoria, $caja, [
        'codigo_barras' => '7500000000141',
        'nombre_producto' => 'Producto Modal',
    ]);

    $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('inventario.productos'))
        ->assertOk()
        ->assertSee('@confirmar-accion.window', false)
        ->assertSee('data-modo="eliminar"', false)
        ->assertSee('data-modo="desactivar"', false)
        ->assertSee('pendiente?.ruta', false)
        ->assertSee('name="_method"', false)
        ->assertDontSee('onsubmit="return confirm(', false);
});

test('la edicion elimina las presentaciones que ya no se envian', function () {
    [$sucursal, $categoria, $caja, $blister] = crearContextoCatalogo($this);

    $producto = crearProductoCatalogo($categoria, $caja);

    $producto->presentacionesPrecio()->create([
        'id_presentacion' => $blister->id,
        'unidades' => 7,
        'precio_presentacion' => 4,
    ]);

    $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->from(route('inventario.productos'))
        ->put(route('inventario.update', $producto), [
            'codigo_barras' => $producto->codigo_barras,
            'nombre_producto' => $producto->nombre_producto,
            'id_categoria' => $categoria->id,
            'presentaciones' => [
                ['id_presentacion' => $caja->id, 'unidades' => 15, 'precio' => 30],
            ],
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('presentacion_producto', [
        'producto' => $producto->id,
        'id_presentacion' => $blister->id,
    ]);

    $this->assertDatabaseHas('presentacion_producto', [
        'producto' => $producto->id,
        'id_presentacion' => $caja->id,
        'unidades' => 15,
        'precio_presentacion' => 30,
    ]);
});
