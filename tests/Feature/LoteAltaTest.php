<?php

use App\Models\Categoria;
use App\Models\Lote;
use App\Models\PresentacionProducto;
use App\Models\Producto;
use App\Models\ProductoPresentacion;
use App\Models\Proveedor;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;

function contextoLoteAlta(object $test): array
{
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Alta Lote',
        'direccion' => 'Av. Alta 1',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $categoria = Categoria::create(['nombre' => 'Analgésicos']);
    $caja = PresentacionProducto::create(['presentacion' => 'Caja', 'descripcion' => '']);
    $proveedor = Proveedor::create([
        'nombre_proveedor' => 'Proveedor Alta Lote',
        'direccion' => 'Av. Alta 9',
        'unidad_entrega' => 'Caja',
        'telefono' => '555100200',
        'correo' => 'proveedor.alta@example.com',
    ]);

    $rol = Rol::firstOrCreate(
        ['tipo_rol' => 'SuperAdmin'],
        ['descripcion' => 'Acceso total']
    );

    $usuario = User::factory()->create([
        'id_sucursal' => $sucursal->id,
        'id_rol' => $rol->id,
    ]);

    $test->actingAs($usuario);

    return [$sucursal, $categoria, $caja, $proveedor];
}

test('un producto sin presentaciones registra el lote en unidades directas', function () {
    [$sucursal, $categoria, , $proveedor] = contextoLoteAlta($this);

    $producto = Producto::create([
        'codigo_barras' => '7506667778881',
        'nombre_producto' => 'Solución fisiológica',
        'descripcion' => '',
        'precio' => 8,
        'vender_por_unidad' => true,
        'id_presentacion' => null,
        'id_categoria' => $categoria->id,
        'es_controlado' => false,
        'es_activo' => true,
    ]);

    $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->post(route('lotes.store'), [
            'folio' => 'L-ALTA-UNI',
            'sucursal' => $sucursal->id,
            'id_proveedor' => $proveedor->id,
            'entregado_en' => '2026-10-01',
            'fecha_caducidad' => '2027-10-01',
            'id_producto' => $producto->id,
            'stock' => 12,
        ])
        ->assertRedirect(route('lotes.index', ['sucursal' => $sucursal->id]));

    $lote = Lote::where('folio', 'L-ALTA-UNI')->firstOrFail();

    expect($lote->id_presentacion)->toBeNull()
        ->and($lote->stock_lote)->toBe(12);

    $this->assertDatabaseHas('inventario', [
        'id_sucursal' => $sucursal->id,
        'id_lote' => $lote->id,
        'stock' => 12,
    ]);
});

test('un producto con presentaciones exige elegir la presentacion del lote', function () {
    [$sucursal, $categoria, $caja, $proveedor] = contextoLoteAlta($this);

    $producto = Producto::create([
        'codigo_barras' => '7506667779994',
        'nombre_producto' => 'Ibuprofeno 400mg',
        'descripcion' => '',
        'precio' => null,
        'vender_por_unidad' => false,
        'id_presentacion' => $caja->id,
        'id_categoria' => $categoria->id,
        'es_controlado' => false,
        'es_activo' => true,
    ]);

    $producto->presentacionesPrecio()->create([
        'id_presentacion' => $caja->id,
        'unidades' => 10,
        'precio_presentacion' => 90,
    ]);

    $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->from(route('lotes.create'))
        ->post(route('lotes.store'), [
            'folio' => 'L-ALTA-SINP',
            'sucursal' => $sucursal->id,
            'id_proveedor' => $proveedor->id,
            'entregado_en' => '2026-10-01',
            'fecha_caducidad' => '2027-10-01',
            'id_producto' => $producto->id,
            'stock' => 3,
        ])
        ->assertSessionHasErrors('id_presentacion');

    $this->assertDatabaseMissing('lotes', ['folio' => 'L-ALTA-SINP']);
});

test('el alta de lote convierte la presentacion elegida a unidades base', function () {
    [$sucursal, $categoria, $caja, $proveedor] = contextoLoteAlta($this);

    $producto = Producto::create([
        'codigo_barras' => '7506667777778',
        'nombre_producto' => 'Amoxicilina 500mg',
        'descripcion' => '',
        'precio' => null,
        'vender_por_unidad' => false,
        'id_presentacion' => $caja->id,
        'id_categoria' => $categoria->id,
        'es_controlado' => false,
        'es_activo' => true,
    ]);

    ProductoPresentacion::create([
        'id_presentacion' => $caja->id,
        'producto' => $producto->id,
        'precio_presentacion' => 90,
        'unidades' => 30,
    ]);

    $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->post(route('lotes.store'), [
            'folio' => 'L-ALTA-CONV',
            'sucursal' => $sucursal->id,
            'id_proveedor' => $proveedor->id,
            'entregado_en' => '2026-10-01',
            'fecha_caducidad' => '2027-10-01',
            'id_producto' => $producto->id,
            'id_presentacion' => $caja->id,
            'stock' => 5,
        ])
        ->assertRedirect(route('lotes.index', ['sucursal' => $sucursal->id]));

    $lote = Lote::where('folio', 'L-ALTA-CONV')->firstOrFail();

    // 5 Cajas x 30 uds = 150 unidades base en lote e inventario.
    expect($lote->stock_lote)->toBe(150)
        ->and((int) $lote->id_presentacion)->toBe($caja->id);

    $this->assertDatabaseHas('inventario', [
        'id_sucursal' => $sucursal->id,
        'id_lote' => $lote->id,
        'stock_individual' => 150,
    ]);
});

test('el formulario de lote muestra la equivalencia de cajas al capturar la cantidad', function () {
    [$sucursal, $categoria, $caja] = contextoLoteAlta($this);

    $producto = Producto::create([
        'codigo_barras' => '7506667776665',
        'nombre_producto' => 'Amoxicilina 500mg',
        'descripcion' => '',
        'precio' => null,
        'vender_por_unidad' => false,
        'id_presentacion' => $caja->id,
        'id_categoria' => $categoria->id,
        'es_controlado' => false,
        'es_activo' => true,
    ]);

    $producto->presentacionesPrecio()->create([
        'id_presentacion' => $caja->id,
        'unidades' => 30,
        'precio_presentacion' => 90,
    ]);

    $contenido = $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('lotes.create'))
        ->assertOk()
        ->getContent();

    $this->assertStringContainsString($producto->nombre_producto, $contenido);
    $this->assertStringContainsString('x-model="cantidad"', $contenido);
    $this->assertStringContainsString('get equivalencia()', $contenido);
    $this->assertStringContainsString('${cantidad} × ${elegida.nombre}', $contenido);
});

test('el alta de lote exige el proveedor', function () {
    [$sucursal, $categoria, $caja] = contextoLoteAlta($this);

    $producto = Producto::create([
        'codigo_barras' => '7506667775556',
        'nombre_producto' => 'Naproxeno 200mg',
        'descripcion' => '',
        'precio' => null,
        'vender_por_unidad' => false,
        'id_presentacion' => $caja->id,
        'id_categoria' => $categoria->id,
        'es_controlado' => false,
        'es_activo' => true,
    ]);

    $producto->presentacionesPrecio()->create([
        'id_presentacion' => $caja->id,
        'unidades' => 10,
        'precio_presentacion' => 45,
    ]);

    $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->post(route('lotes.store'), [
            'folio' => 'L-ALTA-NOPROV',
            'sucursal' => $sucursal->id,
            'entregado_en' => '2026-10-01',
            'fecha_caducidad' => '2027-10-01',
            'id_producto' => $producto->id,
            'id_presentacion' => $caja->id,
            'stock' => 3,
        ])
        ->assertSessionHasErrors(['id_proveedor' => 'Selecciona el proveedor del lote.']);

    $this->assertDatabaseMissing('lotes', ['folio' => 'L-ALTA-NOPROV']);
});

test('el alta de lote usa la sucursal activa del sidebar y no la sucursal enviada', function () {
    [$sucursalA, $categoria, , $proveedor] = contextoLoteAlta($this);

    $sucursalB = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Otra',
        'direccion' => 'Av. Otra 3',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $producto = Producto::create([
        'codigo_barras' => '7506667774447',
        'nombre_producto' => 'Solución salina',
        'descripcion' => '',
        'precio' => 6,
        'vender_por_unidad' => true,
        'id_presentacion' => null,
        'id_categoria' => $categoria->id,
        'es_controlado' => false,
        'es_activo' => true,
    ]);

    $this->withSession(['active_sucursal_id' => $sucursalA->id])
        ->post(route('lotes.store'), [
            'folio' => 'L-ALTA-SUC',
            'id_proveedor' => $proveedor->id,
            'sucursal' => $sucursalB->id,
            'entregado_en' => '2026-10-01',
            'fecha_caducidad' => '2027-10-01',
            'id_producto' => $producto->id,
            'stock' => 12,
        ])
        ->assertRedirect(route('lotes.index', ['sucursal' => $sucursalA->id]));

    $lote = Lote::where('folio', 'L-ALTA-SUC')->firstOrFail();

    $this->assertDatabaseHas('inventario', [
        'id_sucursal' => $sucursalA->id,
        'id_lote' => $lote->id,
        'stock' => 12,
    ]);

    $this->assertDatabaseMissing('inventario', [
        'id_sucursal' => $sucursalB->id,
        'id_lote' => $lote->id,
    ]);
});

test('el formulario de lote fija la sucursal, muestra la caja y marca los productos unitarios', function () {
    [$sucursal] = contextoLoteAlta($this);

    $contenido = $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('lotes.create'))
        ->assertOk()
        ->getContent();

    // La sucursal no se elige en el formulario: se muestra fija y se envía oculta.
    $this->assertStringContainsString('name="sucursal"', $contenido);
    $this->assertStringNotContainsString('<select name="sucursal"', $contenido);
    $this->assertStringContainsString($sucursal->nombre_sucursal, $contenido);

    // La presentación tampoco se elige: se envía oculta y se muestra solo lectura.
    $this->assertStringContainsString('name="id_presentacion"', $contenido);
    $this->assertStringNotContainsString('<select name="id_presentacion"', $contenido);

    // Sin presentaciones el producto se muestra como unitario.
    $this->assertStringContainsString("'Producto unitario'", $contenido);
});

test('el producto aporta su caja como presentacion del lote', function () {
    [$sucursal, $categoria, $caja] = contextoLoteAlta($this);

    $blister = PresentacionProducto::create(['presentacion' => 'Blíster', 'descripcion' => '']);

    $producto = Producto::create([
        'codigo_barras' => '7506667773338',
        'nombre_producto' => 'Amoxicilina 500mg',
        'descripcion' => '',
        'precio' => null,
        'vender_por_unidad' => false,
        'id_presentacion' => $caja->id,
        'id_categoria' => $categoria->id,
        'es_controlado' => false,
        'es_activo' => true,
    ]);

    $producto->presentacionesPrecio()->create([
        'id_presentacion' => $caja->id,
        'unidades' => 10,
        'precio_presentacion' => 90,
    ]);

    $producto->presentacionesPrecio()->create([
        'id_presentacion' => $blister->id,
        'unidades' => 4,
        'precio_presentacion' => 40,
    ]);

    $unitario = Producto::create([
        'codigo_barras' => '7506667772229',
        'nombre_producto' => 'Solución fisiológica',
        'descripcion' => '',
        'precio' => 8,
        'vender_por_unidad' => true,
        'id_presentacion' => null,
        'id_categoria' => $categoria->id,
        'es_controlado' => false,
        'es_activo' => true,
    ]);

    // Aunque tenga más presentaciones, el lote siempre usa la caja.
    $this->assertSame($caja->id, $producto->presentacionParaLote()?->id_presentacion);
    $this->assertNull($unitario->presentacionParaLote());

    $contenido = $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('lotes.create'))
        ->assertOk()
        ->getContent();

    $this->assertStringContainsString('"nombre":"Caja"', $contenido);
    $this->assertStringContainsString('"unidades":10', $contenido);
});
