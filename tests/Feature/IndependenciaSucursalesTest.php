<?php

use App\Models\Categoria;
use App\Models\Inventario;
use App\Models\Lote;
use App\Models\PresentacionProducto;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\Traspaso;
use App\Models\User;

/**
 * Contexto: dos sucursales (Sur y Norte), usuario SuperAdmin de Sur,
 * categoría y presentación Caja.
 *
 * @return array{sur: Sucursal, norte: Sucursal, usuario: User, categoria: Categoria, caja: PresentacionProducto}
 */
function contextoIndependenciaSucursal(): array
{
    $sur = Sucursal::create([
        'nombre_sucursal' => 'Independencia Sur',
        'direccion' => 'Calle Sur 1',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $norte = Sucursal::create([
        'nombre_sucursal' => 'Independencia Norte',
        'direccion' => 'Calle Norte 2',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $categoria = Categoria::create(['nombre' => 'Analgésicos Ind']);
    $caja = PresentacionProducto::create(['presentacion' => 'Caja', 'descripcion' => '']);
    $rol = Rol::firstOrCreate(['tipo_rol' => 'SuperAdmin'], ['descripcion' => 'Acceso total']);
    $usuario = User::factory()->create(['id_rol' => $rol->id, 'id_sucursal' => $sur->id]);

    return compact('sur', 'norte', 'usuario', 'categoria', 'caja');
}

/**
 * Crea un producto activo con presentación Caja de 10 uds.
 *
 * Entrada: categoría, presentación Caja, código de barras y nombre.
 * Salida: el producto creado (sin asociar a sucursales).
 */
function productoIndependencia(Categoria $categoria, PresentacionProducto $caja, string $codigo, string $nombre): Producto
{
    $producto = Producto::create([
        'codigo_barras' => $codigo,
        'nombre_producto' => $nombre,
        'descripcion' => '',
        'stock' => 0,
        'precio' => null,
        'id_presentacion' => $caja->id,
        'id_categoria' => $categoria->id,
        'es_controlado' => false,
        'es_activo' => true,
    ]);

    $producto->presentacionesPrecio()->create([
        'id_presentacion' => $caja->id,
        'unidades' => 10,
        'precio_presentacion' => 25.0,
    ]);

    return $producto;
}

/**
 * Crea un lote con su fila de inventario en la sucursal indicada.
 *
 * Entrada: sucursal, producto, folio y existencia en unidades base.
 * Salida: el lote creado.
 */
function loteIndependencia(Sucursal $sucursal, Producto $producto, string $folio, int $stock): Lote
{
    $lote = Lote::create([
        'folio' => $folio,
        'stock_lote' => $stock,
        'id_producto' => $producto->id,
        'fecha_caducidad' => '2027-12-31',
    ]);

    Inventario::create([
        'id_sucursal' => $sucursal->id,
        'id_lote' => $lote->id,
        'stock' => $stock,
    ]);

    return $lote;
}

test('crear producto lo asocia a la sucursal activa sin crear filas de inventario', function () {
    $ctx = contextoIndependenciaSucursal();

    $this->actingAs($ctx['usuario'])
        ->withSession(['active_sucursal_id' => $ctx['sur']->id])
        ->post(route('inventario.store'), [
            'codigo_barras' => '7509998887701',
            'nombre_producto' => 'Loratadina 10mg',
            'id_categoria' => $ctx['categoria']->id,
            'presentaciones' => [
                ['id_presentacion' => $ctx['caja']->id, 'unidades' => 10, 'precio' => 30.0],
            ],
        ])
        ->assertRedirect(route('inventario.productos', ['sucursal' => $ctx['sur']->id]));

    $producto = Producto::where('codigo_barras', '7509998887701')->firstOrFail();

    expect($producto->sucursales()->count())->toBe(1)
        ->and($producto->sucursales()->whereKey($ctx['sur']->id)->exists())->toBeTrue();

    $this->assertDatabaseCount('inventario', 0);

    // Sin inventario no aparece en la pestaña Stock de su propia sucursal.
    $this->actingAs($ctx['usuario'])
        ->withSession(['active_sucursal_id' => $ctx['sur']->id])
        ->get(route('inventario.stock'))
        ->assertOk()
        ->assertDontSee('Loratadina 10mg');
});

test('la pestana productos solo muestra los productos dados de alta en la sucursal', function () {
    $ctx = contextoIndependenciaSucursal();

    $enSur = productoIndependencia($ctx['categoria'], $ctx['caja'], '7509998887718', 'Producto Solo Sur');
    $enSur->sucursales()->attach($ctx['sur']->id);

    $enNorte = productoIndependencia($ctx['categoria'], $ctx['caja'], '7509998887725', 'Producto Solo Norte');
    $enNorte->sucursales()->attach($ctx['norte']->id);

    $this->actingAs($ctx['usuario'])
        ->withSession(['active_sucursal_id' => $ctx['sur']->id])
        ->get(route('inventario.productos'))
        ->assertOk()
        ->assertSee('Producto Solo Sur')
        ->assertDontSee('Producto Solo Norte');

    $this->actingAs($ctx['usuario'])
        ->withSession(['active_sucursal_id' => $ctx['norte']->id])
        ->get(route('inventario.productos'))
        ->assertOk()
        ->assertSee('Producto Solo Norte')
        ->assertDontSee('Producto Solo Sur');
});

test('la pestana stock solo lista productos con inventario real en la sucursal', function () {
    $ctx = contextoIndependenciaSucursal();

    $conExistencia = productoIndependencia($ctx['categoria'], $ctx['caja'], '7509998887732', 'Amoxicilina Ind');
    $conExistencia->sucursales()->attach($ctx['sur']->id);
    loteIndependencia($ctx['sur'], $conExistencia, 'L-IND-1', 12);

    $agotadoEnNorte = productoIndependencia($ctx['categoria'], $ctx['caja'], '7509998887749', 'Naproxeno Ind');
    $agotadoEnNorte->sucursales()->attach($ctx['norte']->id);
    loteIndependencia($ctx['norte'], $agotadoEnNorte, 'L-IND-2', 0);

    // Dado de alta en Sur pero sin lote: no pertenece a la pestaña Stock.
    $soloCatalogo = productoIndependencia($ctx['categoria'], $ctx['caja'], '7509998887756', 'Ibuprofeno Ind');
    $soloCatalogo->sucursales()->attach($ctx['sur']->id);

    $enSur = $this->actingAs($ctx['usuario'])
        ->withSession(['active_sucursal_id' => $ctx['sur']->id])
        ->get(route('inventario.stock'))
        ->assertOk()
        ->getContent();

    expect($enSur)
        ->toContain('Amoxicilina Ind')
        ->toContain('12 uds.')
        ->not->toContain('Naproxeno Ind')
        ->not->toContain('Ibuprofeno Ind');

    $enNorte = $this->actingAs($ctx['usuario'])
        ->withSession(['active_sucursal_id' => $ctx['norte']->id])
        ->get(route('inventario.stock'))
        ->assertOk()
        ->getContent();

    expect($enNorte)
        ->toContain('Naproxeno Ind')
        ->toContain('>0 uds.</td>')
        ->not->toContain('Amoxicilina Ind')
        ->not->toContain('Ibuprofeno Ind');
});

test('el punto de venta solo ofrece productos con inventario en la sucursal activa', function () {
    $ctx = contextoIndependenciaSucursal();

    $deSur = productoIndependencia($ctx['categoria'], $ctx['caja'], '7509998887763', 'Paracetamol POS');
    $deSur->sucursales()->attach($ctx['sur']->id);
    loteIndependencia($ctx['sur'], $deSur, 'L-IND-POS-S', 50);

    $deNorte = productoIndependencia($ctx['categoria'], $ctx['caja'], '7509998887770', 'Diclofenaco POS');
    $deNorte->sucursales()->attach($ctx['norte']->id);
    loteIndependencia($ctx['norte'], $deNorte, 'L-IND-POS-N', 50);

    $this->actingAs($ctx['usuario'])
        ->withSession(['active_sucursal_id' => $ctx['sur']->id])
        ->get(route('punto-venta.index'))
        ->assertOk()
        ->assertSee('Paracetamol POS')
        ->assertDontSee('Diclofenaco POS');

    $this->actingAs($ctx['usuario'])
        ->withSession(['active_sucursal_id' => $ctx['norte']->id])
        ->get(route('punto-venta.index'))
        ->assertOk()
        ->assertSee('Diclofenaco POS')
        ->assertDontSee('Paracetamol POS');
});

test('registrar un lote asocia el producto a la sucursal del lote sin duplicarlo', function () {
    $ctx = contextoIndependenciaSucursal();

    $producto = productoIndependencia($ctx['categoria'], $ctx['caja'], '7509998887787', 'Omeprazol Ind');
    $producto->sucursales()->attach($ctx['sur']->id);

    $proveedor = Proveedor::create([
        'nombre_proveedor' => 'Proveedor Independencia',
        'direccion' => 'Calle 1',
        'unidad_entrega' => 'Caja',
        'telefono' => '5550011',
        'correo' => 'ind@example.com',
    ]);

    $this->actingAs($ctx['usuario'])
        ->withSession(['active_sucursal_id' => $ctx['norte']->id])
        ->post(route('lotes.store'), [
            'folio' => 'L-IND-ALTA',
            'id_proveedor' => $proveedor->id,
            'sucursal' => $ctx['norte']->id,
            'entregado_en' => '2026-10-01',
            'fecha_caducidad' => '2027-10-01',
            'id_producto' => $producto->id,
            'id_presentacion' => $ctx['caja']->id,
            'stock' => 3,
        ])
        ->assertRedirect(route('lotes.index', ['sucursal' => $ctx['norte']->id]));

    // No se duplica el producto: sigue siendo uno, ahora en ambas sucursales.
    $this->assertDatabaseCount('productos', 1);

    expect($producto->sucursales()->count())->toBe(2)
        ->and($producto->sucursales()->whereKey($ctx['norte']->id)->exists())->toBeTrue();

    // 3 cajas x 10 uds = 30 unidades en el stock del destino.
    $this->actingAs($ctx['usuario'])
        ->withSession(['active_sucursal_id' => $ctx['norte']->id])
        ->get(route('inventario.stock'))
        ->assertOk()
        ->assertSee('Omeprazol Ind')
        ->assertSee('30 uds.', false);

    $this->actingAs($ctx['usuario'])
        ->withSession(['active_sucursal_id' => $ctx['norte']->id])
        ->get(route('inventario.productos'))
        ->assertOk()
        ->assertSee('Omeprazol Ind');
});

test('la recepcion de un traspaso asocia el producto a la sucursal receptora', function () {
    $ctx = contextoIndependenciaSucursal();

    $producto = productoIndependencia($ctx['categoria'], $ctx['caja'], '7509998887794', 'Ranitidina Ind');
    $producto->sucursales()->attach($ctx['sur']->id);

    $lote = loteIndependencia($ctx['sur'], $producto, 'L-IND-TRA', 50);

    $traspaso = Traspaso::create([
        'sucursal_a' => $ctx['sur']->id,
        'sucursal_b' => $ctx['norte']->id,
        'pedido_por' => $ctx['usuario']->id,
        'estado' => 'pendiente',
    ]);

    $traspaso->detalles()->create([
        'id_lote' => $lote->id,
        'cantidad' => 20,
    ]);

    $this->actingAs($ctx['usuario'])
        ->withSession(['active_sucursal_id' => $ctx['sur']->id])
        ->post(route('traspasos.enviar', $traspaso))
        ->assertRedirect();

    $this->actingAs($ctx['usuario'])
        ->withSession(['active_sucursal_id' => $ctx['norte']->id])
        ->post(route('traspasos.aceptar', $traspaso))
        ->assertRedirect();

    expect($traspaso->fresh()->estado)->toBe('aceptado')
        ->and($producto->sucursales()->count())->toBe(2)
        ->and($producto->sucursales()->whereKey($ctx['norte']->id)->exists())->toBeTrue();

    $this->actingAs($ctx['usuario'])
        ->withSession(['active_sucursal_id' => $ctx['norte']->id])
        ->get(route('inventario.productos'))
        ->assertOk()
        ->assertSee('Ranitidina Ind');

    $this->actingAs($ctx['usuario'])
        ->withSession(['active_sucursal_id' => $ctx['norte']->id])
        ->get(route('inventario.stock'))
        ->assertOk()
        ->assertSee('Ranitidina Ind')
        ->assertSee('20 uds.', false);
});
