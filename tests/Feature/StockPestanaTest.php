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

/**
 * Crea las dos sucursales, el usuario SuperAdmin, la categoría y la
 * presentación Caja compartidos por los tests de la pestaña Stock.
 *
 * @return array{sucursalA: Sucursal, sucursalB: Sucursal, usuario: User, categoria: Categoria, caja: PresentacionProducto}
 */
function contextoStockPestana(): array
{
    $sucursalA = Sucursal::create([
        'nombre_sucursal' => 'Stock Sur',
        'direccion' => 'Calle Sur 1',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $sucursalB = Sucursal::create([
        'nombre_sucursal' => 'Stock Norte',
        'direccion' => 'Calle Norte 2',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $categoria = Categoria::create(['nombre' => 'Analgésicos']);
    $caja = PresentacionProducto::create(['presentacion' => 'Caja', 'descripcion' => '']);
    $rol = Rol::firstOrCreate(['tipo_rol' => 'SuperAdmin'], ['descripcion' => 'Acceso total']);
    $usuario = User::factory()->create(['id_rol' => $rol->id, 'id_sucursal' => $sucursalA->id]);

    return [
        'sucursalA' => $sucursalA,
        'sucursalB' => $sucursalB,
        'usuario' => $usuario,
        'categoria' => $categoria,
        'caja' => $caja,
    ];
}

/**
 * Crea un producto activo para la pestaña Stock.
 *
 * Entrada: categoría, presentación Caja (null para un producto unitario),
 * código de barras, nombre y descripción opcional.
 * Salida: el producto creado.
 */
function productoStockPestana(Categoria $categoria, ?PresentacionProducto $caja, string $codigo, string $nombre, string $descripcion = ''): Producto
{
    return Producto::create([
        'codigo_barras' => $codigo,
        'nombre_producto' => $nombre,
        'descripcion' => $descripcion,
        'stock' => 0,
        'precio' => null,
        'id_presentacion' => $caja?->id,
        'id_categoria' => $categoria->id,
        'es_controlado' => false,
        'es_activo' => true,
    ]);
}

/**
 * Crea un lote con su fila de inventario en la sucursal indicada.
 *
 * Entrada: sucursal, producto, folio, existencia en unidades base y
 * presentación del lote (opcional).
 * Salida: el lote creado.
 */
function loteStockPestana(Sucursal $sucursal, Producto $producto, string $folio, int $stock, ?int $idPresentacion = null): Lote
{
    $lote = Lote::create([
        'folio' => $folio,
        'stock_lote' => $stock,
        'id_producto' => $producto->id,
        'id_presentacion' => $idPresentacion,
        'fecha_caducidad' => '2027-12-31',
    ]);

    Inventario::create([
        'id_sucursal' => $sucursal->id,
        'id_lote' => $lote->id,
        'stock' => $stock,
    ]);

    return $lote;
}

test('la pestana stock muestra el codigo debajo del producto y omite descripcion y precio', function () {
    $contexto = contextoStockPestana();

    $producto = productoStockPestana(
        $contexto['categoria'],
        $contexto['caja'],
        '7500000000310',
        'Paracetamol Stock',
        'Analgésico de prueba'
    );

    loteStockPestana($contexto['sucursalA'], $producto, 'L-STOCK-CODIGO', 12);

    $contenido = $this->actingAs($contexto['usuario'])
        ->withSession(['active_sucursal_id' => $contexto['sucursalA']->id])
        ->get(route('inventario.stock'))
        ->assertOk()
        ->getContent();

    expect($contenido)
        ->toMatch('/Paracetamol Stock<\/span>\s*<span class="block text-xs theme-subtle">7500000000310<\/span>/')
        ->not->toContain('Analgésico de prueba')
        ->not->toContain('Descripción')
        ->not->toContain('Precio');
});

test('muestra las cajas cerradas, la caja abierta y el stock en unidades', function () {
    $contexto = contextoStockPestana();
    $producto = productoStockPestana($contexto['categoria'], $contexto['caja'], '7500000000311', 'Ibuprofeno Stock');

    ProductoPresentacion::create([
        'id_presentacion' => $contexto['caja']->id,
        'producto' => $producto->id,
        'precio_presentacion' => 90.0,
        'unidades' => 10,
    ]);

    loteStockPestana($contexto['sucursalA'], $producto, 'L-STOCK-CAJA', 105, $contexto['caja']->id);

    $contenido = $this->actingAs($contexto['usuario'])
        ->withSession(['active_sucursal_id' => $contexto['sucursalA']->id])
        ->get(route('inventario.stock'))
        ->assertOk()
        ->getContent();

    expect($contenido)
        ->toContain('>10</span>')
        ->toContain('10 uds. por caja')
        ->toContain('>5 uds.</td>')
        ->toContain('105 uds.');
});

test('un producto sin presentaciones muestra guion en cajas y sus unidades', function () {
    $contexto = contextoStockPestana();
    $producto = productoStockPestana($contexto['categoria'], null, '7500000000312', 'Suero Fisiológico');

    loteStockPestana($contexto['sucursalA'], $producto, 'L-STOCK-UNIT', 40);

    $contenido = $this->actingAs($contexto['usuario'])
        ->withSession(['active_sucursal_id' => $contexto['sucursalA']->id])
        ->get(route('inventario.stock'))
        ->assertOk()
        ->getContent();

    expect($contenido)
        ->toContain('40 uds.')
        ->toContain('—')
        ->not->toContain('uds. por caja');
});

test('cuenta los lotes con existencia de la sucursal activa', function () {
    $contexto = contextoStockPestana();
    $producto = productoStockPestana($contexto['categoria'], $contexto['caja'], '7500000000313', 'Naproxeno Stock');

    // Sucursal A: un lote agotado y otro vigente → solo el vigente cuenta.
    loteStockPestana($contexto['sucursalA'], $producto, 'L-STOCK-A1', 0);
    loteStockPestana($contexto['sucursalA'], $producto, 'L-STOCK-A2', 6);

    // Sucursal B: dos lotes vigentes.
    loteStockPestana($contexto['sucursalB'], $producto, 'L-STOCK-B1', 7);
    loteStockPestana($contexto['sucursalB'], $producto, 'L-STOCK-B2', 3);

    $cliente = $this->actingAs($contexto['usuario']);

    $enSur = $cliente->withSession(['active_sucursal_id' => $contexto['sucursalA']->id])
        ->get(route('inventario.stock'))
        ->assertOk()
        ->getContent();

    expect($enSur)
        ->toContain('1 lote')
        ->toContain('6 uds.')
        ->toContain(e(route('lotes.index', ['sucursal' => $contexto['sucursalA']->id, 'buscar' => 'Naproxeno Stock'])))
        ->not->toContain('2 lotes');

    // El query string ?sucursal= cambia la sucursal visible (middleware).
    $enNorte = $cliente->withSession(['active_sucursal_id' => $contexto['sucursalA']->id])
        ->get(route('inventario.stock', ['sucursal' => $contexto['sucursalB']->id]))
        ->assertOk()
        ->getContent();

    expect($enNorte)
        ->toContain('2 lotes')
        ->toContain('10 uds.')
        ->toContain(e(route('lotes.index', ['sucursal' => $contexto['sucursalB']->id, 'buscar' => 'Naproxeno Stock'])))
        ->not->toContain('1 lote');
});

test('marca bajo o disponible segun la existencia y omite los productos sin inventario en la sucursal', function () {
    $contexto = contextoStockPestana();

    $bajo = productoStockPestana($contexto['categoria'], $contexto['caja'], '7500000000314', 'Amoxicilina Stock');
    loteStockPestana($contexto['sucursalA'], $bajo, 'L-STOCK-BAJO', 5);

    $disponible = productoStockPestana($contexto['categoria'], $contexto['caja'], '7500000000315', 'Diclofenaco Stock');
    loteStockPestana($contexto['sucursalA'], $disponible, 'L-STOCK-DISP', 40);

    // Sin filas de inventario en la sucursal activa: el producto no pertenece
    // a la pestaña Stock (el catálogo es más ancho que la existencia).
    productoStockPestana($contexto['categoria'], $contexto['caja'], '7500000000316', 'Omeprazol Stock');

    $contenido = $this->actingAs($contexto['usuario'])
        ->withSession(['active_sucursal_id' => $contexto['sucursalA']->id])
        ->get(route('inventario.stock'))
        ->assertOk()
        ->getContent();

    expect($contenido)
        ->not->toContain('Omeprazol Stock')
        ->not->toContain('Sin registro')
        ->toContain('inventory-status-low')
        ->toContain('inventory-status-available')
        ->toMatch('/Productos<\/span>\s*<span class="stat-pill positive">2<\/span>/')
        ->toMatch('/Stock crítico<\/span>\s*<span class="stat-pill warning">1<\/span>/');
});

test('la caja abierta muestra guion cuando el stock es multiplo de la caja', function () {
    $contexto = contextoStockPestana();
    $producto = productoStockPestana($contexto['categoria'], $contexto['caja'], '7500000000317', 'Aspirina Stock');

    ProductoPresentacion::create([
        'id_presentacion' => $contexto['caja']->id,
        'producto' => $producto->id,
        'precio_presentacion' => 80.0,
        'unidades' => 10,
    ]);

    loteStockPestana($contexto['sucursalA'], $producto, 'L-STOCK-MULT', 200, $contexto['caja']->id);

    $contenido = $this->actingAs($contexto['usuario'])
        ->withSession(['active_sucursal_id' => $contexto['sucursalA']->id])
        ->get(route('inventario.stock'))
        ->assertOk()
        ->getContent();

    expect($contenido)
        ->toContain('>20</span>')
        ->toContain('10 uds. por caja')
        ->toContain('200 uds.')
        ->toContain('>—</span>');
});
