<?php

use App\Models\Categoria;
use App\Models\Inventario;
use App\Models\Lote;
use App\Models\PresentacionProducto;
use App\Models\Producto;
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

test('una compra de un producto sin presentaciones registra el lote en unidades directas', function () {
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
        ->post(route('compras.store'), [
            'id_proveedor' => $proveedor->id,
            'id_sucursal' => $sucursal->id,
            'lotes' => [
                [
                    'id_producto' => $producto->id,
                    'cantidad' => 12,
                    'fecha_entrada' => '2026-10-01',
                    'fecha_caducidad' => '2027-10-01',
                ],
            ],
        ])
        ->assertRedirect(route('entradas-de-almacen.index', [
            'sucursal' => $sucursal->id,
            'tipo' => 'compras',
        ]));

    $lote = Lote::latest('id')->firstOrFail();

    expect($lote->id_presentacion)->toBeNull()
        ->and((int) $lote->stock_lote)->toBe(12);

    $this->assertDatabaseHas('inventario', [
        'id_sucursal' => $sucursal->id,
        'id_lote' => $lote->id,
        'stock' => 12,
    ]);
});

test('la compra convierte la presentacion principal del producto a unidades base', function () {
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

    $producto->presentacionesPrecio()->create([
        'id_presentacion' => $caja->id,
        'unidades' => 30,
        'precio_presentacion' => 90,
        'es_principal' => true,
    ]);

    $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->post(route('compras.store'), [
            'id_proveedor' => $proveedor->id,
            'id_sucursal' => $sucursal->id,
            'lotes' => [
                [
                    'id_producto' => $producto->id,
                    'cantidad' => 5,
                    'fecha_entrada' => '2026-10-01',
                    'fecha_caducidad' => '2027-10-01',
                ],
            ],
        ])
        ->assertRedirect();

    $lote = Lote::latest('id')->firstOrFail();

    // 5 Cajas x 30 uds = 150 unidades base en lote e inventario.
    expect((int) $lote->stock_lote)->toBe(150)
        ->and((int) $lote->id_presentacion)->toBe((int) $caja->id);

    $this->assertDatabaseHas('inventario', [
        'id_sucursal' => $sucursal->id,
        'id_lote' => $lote->id,
        'stock_individual' => 150,
    ]);
});

test('el formulario de compra muestra la equivalencia de cajas al capturar la cantidad', function () {
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
        'es_principal' => true,
    ]);

    $contenido = $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('compras.create'))
        ->assertOk()
        ->getContent();

    $this->assertStringContainsString($producto->nombre_producto, $contenido);
    $this->assertStringContainsString('presentacion(row.producto)', $contenido);
    $this->assertStringContainsString('"unidades":30', $contenido);
});

test('la compra exige el proveedor', function () {
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
        'es_principal' => true,
    ]);

    $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->post(route('compras.store'), [
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
        ->assertSessionHasErrors('id_proveedor');

    $this->assertDatabaseCount('compras', 0);
    $this->assertDatabaseCount('lotes', 0);
});

test('la compra usa la sucursal enviada y no mueve inventario de otra sucursal', function () {
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
        ->post(route('compras.store'), [
            'id_proveedor' => $proveedor->id,
            'id_sucursal' => $sucursalB->id,
            'lotes' => [
                [
                    'id_producto' => $producto->id,
                    'cantidad' => 12,
                    'fecha_entrada' => '2026-10-01',
                    'fecha_caducidad' => '2027-10-01',
                ],
            ],
        ])
        ->assertRedirect(route('entradas-de-almacen.index', [
            'sucursal' => $sucursalB->id,
            'tipo' => 'compras',
        ]));

    $lote = Lote::latest('id')->firstOrFail();

    $this->assertDatabaseHas('inventario', [
        'id_sucursal' => $sucursalB->id,
        'id_lote' => $lote->id,
        'stock' => 12,
    ]);

    $this->assertDatabaseMissing('inventario', [
        'id_sucursal' => $sucursalA->id,
        'id_lote' => $lote->id,
    ]);
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
        'id_presentacion' => $blister->id,
        'unidades' => 4,
        'precio_presentacion' => 40,
    ]);

    $producto->presentacionesPrecio()->create([
        'id_presentacion' => $caja->id,
        'unidades' => 10,
        'precio_presentacion' => 90,
        'es_principal' => true,
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

    // Aunque tenga más presentaciones, el lote siempre usa la principal (caja).
    $this->assertSame($caja->id, $producto->presentacionParaLote()?->id_presentacion);
    $this->assertNull($unitario->presentacionParaLote());

    $contenido = $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('compras.create'))
        ->assertOk()
        ->getContent();

    $this->assertStringContainsString('"nombre":"Caja"', $contenido);
    $this->assertStringContainsString('"unidades":10', $contenido);
});
