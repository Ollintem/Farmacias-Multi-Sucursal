<?php

use App\Models\Inventario;
use App\Models\Lote;
use App\Models\PresentacionProducto;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;

/**
 * Crea la sucursal, el usuario SuperAdmin y el producto compartidos
 * por los tests de coherencia de las tarjetas de Lotes.
 *
 * @return array{sucursal: Sucursal, usuario: User, producto: Producto}
 */
function escenarioCoherenciaTarjetas(): array
{
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Lotes Coherencia',
        'direccion' => 'Calle Coherencia 1',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $rol = Rol::firstOrCreate(['tipo_rol' => 'SuperAdmin'], ['descripcion' => 'Acceso total']);
    $usuario = User::factory()->create(['id_rol' => $rol->id, 'id_sucursal' => $sucursal->id]);

    $caja = PresentacionProducto::create(['presentacion' => 'Caja', 'descripcion' => '']);

    $producto = Producto::create([
        'codigo_barras' => '7500000000531',
        'nombre_producto' => 'Producto Coherencia Demo',
        'descripcion' => '',
        'stock' => 0,
        'precio' => 10.0,
        'id_presentacion' => $caja->id,
        'es_controlado' => false,
        'es_activo' => true,
    ]);

    return ['sucursal' => $sucursal, 'usuario' => $usuario, 'producto' => $producto];
}

/**
 * Crea un lote con su fila de inventario en la sucursal.
 *
 * Entrada: sucursal, producto, folio, fecha (null = sin fecha),
 * unidades de entrada (`stock_lote`) y existencia actual.
 * Salida: el lote creado.
 */
function loteCoherenciaTarjetas(Sucursal $sucursal, Producto $producto, string $folio, ?string $fecha, int $entrada, int $restante): Lote
{
    $lote = Lote::create([
        'folio' => $folio,
        'stock_lote' => $entrada,
        'id_producto' => $producto->id,
        'fecha_caducidad' => $fecha,
    ]);

    Inventario::create([
        'id_sucursal' => $sucursal->id,
        'id_lote' => $lote->id,
        'stock' => $restante,
    ]);

    return $lote;
}

test('las cuatro tarjetas cuentan el mismo conjunto y suman el total', function () {
    $escenario = escenarioCoherenciaTarjetas();
    $sucursal = $escenario['sucursal'];
    $producto = $escenario['producto'];

    loteCoherenciaTarjetas($sucursal, $producto, 'L-COHE-VIG', now()->addDays(400)->format('Y-m-d'), 10, 10);
    // Agotado con fecha lejana: en Lotes se lista por su fecha (Vigentes).
    loteCoherenciaTarjetas($sucursal, $producto, 'L-COHE-AGOVIG', now()->addDays(300)->format('Y-m-d'), 10, 0);
    // Crítico agotado: sigue contando en Por caducar.
    loteCoherenciaTarjetas($sucursal, $producto, 'L-COHE-CRIT', now()->addDays(10)->format('Y-m-d'), 5, 0);
    // Sin fecha: cae en Por caducar (atención pendiente).
    loteCoherenciaTarjetas($sucursal, $producto, 'L-COHE-SINF', null, 4, 4);
    loteCoherenciaTarjetas($sucursal, $producto, 'L-COHE-CAD', now()->subDays(100)->format('Y-m-d'), 2, 2);

    // Partición: cada lote cae en exacto un cubeta → la suma de las tres
    // tarjetas de estado siempre iguala Total de lotes (5 = 2 + 2 + 1).
    $this->actingAs($escenario['usuario'])
        ->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('lotes.index'))
        ->assertOk()
        ->assertViewHas('totalLotes', 5)
        ->assertViewHas('vigentes', 2)
        ->assertViewHas('porCaducar', 2)
        ->assertViewHas('caducados', 1);

    // El filtro Por caducar muestra el sin fecha y el crítico, nada más.
    $this->actingAs($escenario['usuario'])
        ->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('lotes.index', ['estado' => 'por-caducar']))
        ->assertOk()
        ->assertSee('L-COHE-CRIT')
        ->assertSee('L-COHE-SINF')
        ->assertDontSee('L-COHE-VIG')
        ->assertDontSee('L-COHE-AGOVIG')
        ->assertDontSee('L-COHE-CAD');

    // El filtro Vigentes incluye el agotado con fecha lejana.
    $this->actingAs($escenario['usuario'])
        ->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('lotes.index', ['estado' => 'vigentes']))
        ->assertOk()
        ->assertSee('L-COHE-VIG')
        ->assertSee('L-COHE-AGOVIG')
        ->assertDontSee('L-COHE-CRIT')
        ->assertDontSee('L-COHE-SINF')
        ->assertDontSee('L-COHE-CAD');
});

test('el lote anulado cuenta en caducados y no rompe la suma de tarjetas', function () {
    $escenario = escenarioCoherenciaTarjetas();
    $sucursal = $escenario['sucursal'];
    $producto = $escenario['producto'];

    $anulado = loteCoherenciaTarjetas($sucursal, $producto, 'L-COHE-ANU', now()->addDays(400)->format('Y-m-d'), 6, 0);
    loteCoherenciaTarjetas($sucursal, $producto, 'L-COHE-VIG2', now()->addDays(400)->format('Y-m-d'), 6, 6);

    $this->actingAs($escenario['usuario'])
        ->withSession(['active_sucursal_id' => $sucursal->id])
        ->post(route('lotes.anular', $anulado))
        ->assertSessionHas('success');

    // 2 = 1 vigente + 0 por caducar + 1 caducado (el anulado).
    $this->actingAs($escenario['usuario'])
        ->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('lotes.index'))
        ->assertOk()
        ->assertSee('Anulado')
        ->assertViewHas('totalLotes', 2)
        ->assertViewHas('vigentes', 1)
        ->assertViewHas('porCaducar', 0)
        ->assertViewHas('caducados', 1);
});
