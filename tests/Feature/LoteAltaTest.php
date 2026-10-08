<?php

use App\Models\Categoria;
use App\Models\Lote;
use App\Models\PresentacionProducto;
use App\Models\Producto;
use App\Models\ProductoPresentacion;
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

    $rol = Rol::firstOrCreate(
        ['tipo_rol' => 'SuperAdmin'],
        ['descripcion' => 'Acceso total']
    );

    $usuario = User::factory()->create([
        'id_sucursal' => $sucursal->id,
        'id_rol' => $rol->id,
    ]);

    $test->actingAs($usuario);

    return [$sucursal, $categoria, $caja];
}

test('un producto sin presentaciones registra el lote en unidades directas', function () {
    [$sucursal, $categoria] = contextoLoteAlta($this);

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
    [$sucursal, $categoria, $caja] = contextoLoteAlta($this);

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
            'entregado_en' => '2026-10-01',
            'fecha_caducidad' => '2027-10-01',
            'id_producto' => $producto->id,
            'stock' => 3,
        ])
        ->assertSessionHasErrors('id_presentacion');

    $this->assertDatabaseMissing('lotes', ['folio' => 'L-ALTA-SINP']);
});

test('el alta de lote convierte la presentacion elegida a unidades base', function () {
    [$sucursal, $categoria, $caja] = contextoLoteAlta($this);

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
        'stock' => 150,
    ]);
});
