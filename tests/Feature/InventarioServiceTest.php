<?php

use App\Models\Categoria;
use App\Models\Inventario;
use App\Models\Lote;
use App\Models\PresentacionProducto;
use App\Models\Producto;
use App\Models\ProductoPresentacion;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;
use App\Services\InventarioService;
use Carbon\Carbon;

/**
 * @return array{sucursalA: Sucursal, sucursalB: Sucursal, categoria: Categoria, caja: PresentacionProducto}
 */
function contextoInventarioService(): array
{
    $sucursalA = Sucursal::create([
        'nombre_sucursal' => 'Servicio Inventario A',
        'direccion' => 'Calle Servicio A',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $sucursalB = Sucursal::create([
        'nombre_sucursal' => 'Servicio Inventario B',
        'direccion' => 'Calle Servicio B',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    return [
        'sucursalA' => $sucursalA,
        'sucursalB' => $sucursalB,
        'categoria' => Categoria::create(['nombre' => 'Servicio Inventario']),
        'caja' => PresentacionProducto::create(['presentacion' => 'Caja', 'descripcion' => '']),
    ];
}

function productoInventarioService(
    Categoria $categoria,
    PresentacionProducto $caja,
    string $codigo,
    string $nombre,
    bool $activo = true,
): Producto {
    return Producto::create([
        'codigo_barras' => $codigo,
        'nombre_producto' => $nombre,
        'descripcion' => '',
        'id_presentacion' => $caja->id,
        'id_categoria' => $categoria->id,
        'es_controlado' => false,
        'es_activo' => $activo,
    ]);
}

function loteInventarioService(
    Sucursal $sucursal,
    Producto $producto,
    string $folio,
    int $stock,
    ?string $fechaCaducidad,
): Lote {
    $lote = Lote::create([
        'folio' => $folio,
        'stock_lote' => $stock,
        'id_producto' => $producto->id,
        'fecha_caducidad' => $fechaCaducidad,
    ]);

    Inventario::create([
        'id_sucursal' => $sucursal->id,
        'id_lote' => $lote->id,
        'stock_individual' => $stock,
    ]);

    return $lote;
}

function presentacionInventarioService(
    Producto $producto,
    PresentacionProducto $presentacion,
    int $stockMinimo,
    bool $principal = true,
): ProductoPresentacion {
    return $producto->presentacionesPrecio()->create([
        'id_presentacion' => $presentacion->id,
        'unidades' => 10,
        'precio_presentacion' => 90,
        'stock_minimo' => $stockMinimo,
        'es_principal' => $principal,
    ]);
}

test('busca productos activos por nombre parcial y código exacto sin duplicar presentaciones', function () {
    $contexto = contextoInventarioService();
    $servicio = app(InventarioService::class);

    $paracetamol = productoInventarioService(
        $contexto['categoria'],
        $contexto['caja'],
        '7500000000219',
        'Paracetamol Servicio'
    );
    $paracetamol->sucursales()->attach($contexto['sucursalA']->id);
    loteInventarioService($contexto['sucursalA'], $paracetamol, 'L-SERV-1', 5, '2027-01-01');

    $blister = PresentacionProducto::create(['presentacion' => 'Blíster', 'descripcion' => '']);
    presentacionInventarioService($paracetamol, $blister, 0, false);

    $ibuprofeno = productoInventarioService(
        $contexto['categoria'],
        $contexto['caja'],
        '7500000000226',
        'Ibuprofeno Servicio'
    );
    $ibuprofeno->sucursales()->attach($contexto['sucursalA']->id);
    loteInventarioService($contexto['sucursalA'], $ibuprofeno, 'L-SERV-2', 5, '2027-01-01');

    $inactivo = productoInventarioService(
        $contexto['categoria'],
        $contexto['caja'],
        '7500000000233',
        'Paracetamol Inactivo',
        false
    );
    $inactivo->sucursales()->attach($contexto['sucursalA']->id);
    loteInventarioService($contexto['sucursalA'], $inactivo, 'L-SERV-3', 5, '2027-01-01');

    $otraSucursal = productoInventarioService(
        $contexto['categoria'],
        $contexto['caja'],
        '7500000000240',
        'Paracetamol Otra Sucursal'
    );
    $otraSucursal->sucursales()->attach($contexto['sucursalB']->id);
    loteInventarioService($contexto['sucursalB'], $otraSucursal, 'L-SERV-4', 5, '2027-01-01');

    expect(
        $servicio->buscarProductos('paracetamol', $contexto['sucursalA']->id)
            ->pluck('id')
            ->all()
    )->toBe([$paracetamol->id]);

    expect(
        $servicio->buscarProductos('7500000000219', $contexto['sucursalA']->id)
            ->pluck('id')
            ->all()
    )->toBe([$paracetamol->id]);

    expect(
        $servicio->buscarProductos('750000000021', $contexto['sucursalA']->id)
            ->pluck('id')
            ->all()
    )->toBe([]);

    expect(
        $servicio->buscarProductos('750000000021', $contexto['sucursalA']->id, codigoExacto: false)
            ->pluck('id')
            ->all()
    )->toBe([$paracetamol->id]);
});

test('consulta los lotes caducados y próximos a caducar con existencia y por sucursal', function () {
    $this->travelTo(Carbon::parse('2026-11-17 10:00:00'));
    $contexto = contextoInventarioService();
    $servicio = app(InventarioService::class);

    $producto = productoInventarioService(
        $contexto['categoria'],
        $contexto['caja'],
        '7500000000301',
        'Producto Caducidad Servicio'
    );

    loteInventarioService($contexto['sucursalA'], $producto, 'L-CAD-FUTURO', 5, '2027-03-20');
    loteInventarioService($contexto['sucursalA'], $producto, 'L-CAD-CERCA', 6, '2026-12-01');
    loteInventarioService($contexto['sucursalA'], $producto, 'L-CAD-HOY', 7, '2026-11-17');
    loteInventarioService($contexto['sucursalA'], $producto, 'L-CAD-VENCIDO', 8, '2026-11-16');
    loteInventarioService($contexto['sucursalA'], $producto, 'L-CAD-SIN-STOCK', 0, '2026-12-02');
    loteInventarioService($contexto['sucursalB'], $producto, 'L-CAD-OTRA', 9, '2026-12-03');

    $filas = $servicio->productosProximosACaducar($contexto['sucursalA']->id);

    expect($filas->pluck('folio')->all())->toBe([
        'L-CAD-VENCIDO',
        'L-CAD-HOY',
        'L-CAD-CERCA',
    ])
        ->and($filas->first()['estado'])->toBe('CADUCADO')
        ->and($filas->last()['estado'])->toBe('PRÓXIMO A CADUCAR')
        ->and($filas->first()['producto']->is($producto))->toBeTrue()
        ->and($filas->first()['sucursal']->is($contexto['sucursalA']))->toBeTrue()
        ->and($filas->first()['stock'])->toBe(8);
});

test('consulta productos activos con stock igual o inferior al mínimo de su presentación principal', function () {
    $contexto = contextoInventarioService();
    $servicio = app(InventarioService::class);

    $bajo = productoInventarioService(
        $contexto['categoria'],
        $contexto['caja'],
        '7500000000401',
        'Producto Stock Bajo Servicio'
    );
    presentacionInventarioService($bajo, $contexto['caja'], 10);
    loteInventarioService($contexto['sucursalA'], $bajo, 'L-BAJO-SERV', 5, '2027-01-01');

    $limite = productoInventarioService(
        $contexto['categoria'],
        $contexto['caja'],
        '7500000000402',
        'Producto Stock Limite Servicio'
    );
    presentacionInventarioService($limite, $contexto['caja'], 3);
    loteInventarioService($contexto['sucursalA'], $limite, 'L-LIMITE-SERV', 3, '2027-01-01');

    $suficiente = productoInventarioService(
        $contexto['categoria'],
        $contexto['caja'],
        '7500000000403',
        'Producto Stock Suficiente Servicio'
    );
    presentacionInventarioService($suficiente, $contexto['caja'], 3);
    loteInventarioService($contexto['sucursalA'], $suficiente, 'L-SUFICIENTE-SERV', 10, '2027-01-01');

    $inactivo = productoInventarioService(
        $contexto['categoria'],
        $contexto['caja'],
        '7500000000404',
        'Producto Stock Inactivo Servicio',
        false
    );
    presentacionInventarioService($inactivo, $contexto['caja'], 10);
    loteInventarioService($contexto['sucursalA'], $inactivo, 'L-INACTIVO-SERV', 1, '2027-01-01');

    $filas = $servicio->productosConStockBajo($contexto['sucursalA']->id);

    expect($filas->pluck('producto.nombre_producto')->all())->toBe([
        'Producto Stock Limite Servicio',
        'Producto Stock Bajo Servicio',
    ])
        ->and($filas->first()['stock'])->toBe(3)
        ->and($filas->first()['minimo'])->toBe(3)
        ->and($filas->last()['stock'])->toBe(5)
        ->and($filas->last()['minimo'])->toBe(10)
        ->and($filas->last()['sucursal']->is($contexto['sucursalA']))->toBeTrue();
});

test('el dashboard muestra el target de caducidades y stock bajo de la sucursal activa', function () {
    $contexto = contextoInventarioService();

    $porCaducar = productoInventarioService(
        $contexto['categoria'],
        $contexto['caja'],
        '7500000000501',
        'Producto Target Caducidad'
    );
    presentacionInventarioService($porCaducar, $contexto['caja'], 0);
    loteInventarioService(
        $contexto['sucursalA'],
        $porCaducar,
        'L-TARGET-CAD',
        20,
        now()->addDays(10)->toDateString()
    );

    $stockBajo = productoInventarioService(
        $contexto['categoria'],
        $contexto['caja'],
        '7500000000502',
        'Producto Target Stock Bajo'
    );
    presentacionInventarioService($stockBajo, $contexto['caja'], 10);
    loteInventarioService(
        $contexto['sucursalA'],
        $stockBajo,
        'L-TARGET-BAJO',
        2,
        now()->addDays(200)->toDateString()
    );

    $rol = Rol::firstOrCreate(['tipo_rol' => 'SuperAdmin'], ['descripcion' => 'Acceso total']);
    $usuario = User::factory()->create([
        'id_rol' => $rol->id,
        'id_sucursal' => $contexto['sucursalA']->id,
    ]);

    $this->actingAs($usuario)
        ->withSession(['active_sucursal_id' => $contexto['sucursalA']->id])
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Inventario que requiere atención')
        ->assertSee('Próximos a caducar')
        ->assertSee('Stock bajo')
        ->assertSee('Producto Target Caducidad')
        ->assertSee('Producto Target Stock Bajo');
});
