<?php

use App\Models\Inventario;
use App\Models\Lote;
use App\Models\PresentacionProducto;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;
use App\Support\AlertasResumen;

/**
 * Crea la sucursal, el usuario SuperAdmin y el producto compartidos
 * por los tests del semáforo de caducidades.
 *
 * @return array{sucursal: Sucursal, usuario: User, producto: Producto}
 */
function escenarioAlertasCaducidad(): array
{
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Alertas Caducidad',
        'direccion' => 'Calle 22',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $rol = Rol::firstOrCreate(['tipo_rol' => 'SuperAdmin'], ['descripcion' => 'Acceso total']);
    $usuario = User::factory()->create(['id_rol' => $rol->id, 'id_sucursal' => $sucursal->id]);

    $caja = PresentacionProducto::create(['presentacion' => 'Caja', 'descripcion' => '']);

    $producto = Producto::create([
        'codigo_barras' => '7500000000399',
        'nombre_producto' => 'Producto Semaforo Caducidad',
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
 * Crea un lote con su fila de inventario en la sucursal del escenario.
 *
 * Entrada: sucursal, producto y folio del lote; fecha (null = sin fecha)
 * y stock a sembrar (null = sin fila de inventario).
 * Salida: el lote creado.
 */
function loteAlertasCaducidad(Sucursal $sucursal, Producto $producto, string $folio, ?string $fecha, ?int $stock): Lote
{
    $lote = Lote::create([
        'folio' => $folio,
        'stock_lote' => $stock ?? 10,
        'id_producto' => $producto->id,
        'fecha_caducidad' => $fecha,
    ]);

    if ($stock !== null) {
        Inventario::create([
            'id_sucursal' => $sucursal->id,
            'id_lote' => $lote->id,
            'stock' => $stock,
        ]);
    }

    return $lote;
}

/**
 * Lee una sección del centro de notificaciones.
 *
 * Entrada: usuario, sucursal y filtro (todas por defecto; caducidad para el grupo de caducidades).
 * Salida: el HTML de la página de alertas.
 */
function paginaCaducidadesAlertas(User $usuario, Sucursal $sucursal, string $filtro = 'todas'): string
{
    return test()->actingAs($usuario)
        ->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('alertas.index', ['filtro' => $filtro]))
        ->assertOk()
        ->getContent();
}

test('el filtro de caducidad clasifica los lotes con existencia y excluye los sin existencia', function () {
    $escenario = escenarioAlertasCaducidad();

    loteAlertasCaducidad($escenario['sucursal'], $escenario['producto'], 'L-ALERT-CAD', '2020-01-01', 5);
    loteAlertasCaducidad($escenario['sucursal'], $escenario['producto'], 'L-ALERT-CER', now()->addDays(10)->format('Y-m-d'), 5);
    loteAlertasCaducidad($escenario['sucursal'], $escenario['producto'], 'L-ALERT-PORV', now()->addDays(60)->format('Y-m-d'), 5);
    loteAlertasCaducidad($escenario['sucursal'], $escenario['producto'], 'L-ALERT-VIG', now()->addDays(200)->format('Y-m-d'), 5);
    loteAlertasCaducidad($escenario['sucursal'], $escenario['producto'], 'L-ALERT-SIN', null, 5);
    loteAlertasCaducidad($escenario['sucursal'], $escenario['producto'], 'L-ALERT-CERO', now()->addDays(5)->format('Y-m-d'), 0);
    loteAlertasCaducidad($escenario['sucursal'], $escenario['producto'], 'L-ALERT-NOREG', now()->addDays(5)->format('Y-m-d'), null);

    $caducidad = paginaCaducidadesAlertas($escenario['usuario'], $escenario['sucursal'], 'caducidad');

    // Los lotes con existencia aparecen clasificados (rojo/amarillo/verde);
    // los que no tienen inventario en la sucursal se excluyen.
    expect($caducidad)
        ->toContain('L-ALERT-CAD')
        ->toContain('L-ALERT-CER')
        ->toContain('L-ALERT-PORV')
        ->toContain('L-ALERT-VIG')
        ->toContain('Vigente ·')
        ->toContain('L-ALERT-SIN')
        ->toContain('Sin fecha de caducidad')
        ->not->toContain('L-ALERT-CERO')
        ->not->toContain('L-ALERT-NOREG')
        ->not->toContain('Caduca en -');

    $alias = test()->actingAs($escenario['usuario'])
        ->withSession(['active_sucursal_id' => $escenario['sucursal']->id])
        ->get(route('alertas.index', ['seccion' => 'caducidades']))
        ->assertOk()
        ->getContent();

    expect($alias)->toContain('L-ALERT-CAD');
});

test('el badge del sidebar cuenta los mismos rojos que las notificaciones de prioridad alta', function () {
    $escenario = escenarioAlertasCaducidad();

    loteAlertasCaducidad($escenario['sucursal'], $escenario['producto'], 'L-ALERT-CAD', '2020-01-01', 5);
    loteAlertasCaducidad($escenario['sucursal'], $escenario['producto'], 'L-ALERT-CER', now()->addDays(10)->format('Y-m-d'), 5);
    loteAlertasCaducidad($escenario['sucursal'], $escenario['producto'], 'L-ALERT-PORV', now()->addDays(60)->format('Y-m-d'), 5);
    loteAlertasCaducidad($escenario['sucursal'], $escenario['producto'], 'L-ALERT-VIG', now()->addDays(200)->format('Y-m-d'), 5);
    loteAlertasCaducidad($escenario['sucursal'], $escenario['producto'], 'L-ALERT-SIN', null, 5);
    loteAlertasCaducidad($escenario['sucursal'], $escenario['producto'], 'L-ALERT-CERO', now()->addDays(5)->format('Y-m-d'), 0);
    loteAlertasCaducidad($escenario['sucursal'], $escenario['producto'], 'L-ALERT-NOREG', now()->addDays(5)->format('Y-m-d'), null);

    $resumen = AlertasResumen::counts($escenario['sucursal']->id);

    expect($resumen['rojos'])->toBe(2);

    $pagina = paginaCaducidadesAlertas($escenario['usuario'], $escenario['sucursal']);

    expect($pagina)
        ->toContain('L-ALERT-CAD')
        ->toContain('L-ALERT-CER')
        ->not->toContain('L-ALERT-CERO')
        ->not->toContain('L-ALERT-NOREG');

    expect(substr_count($pagina, 'noti-avatar prioridad-alta'))->toBe($resumen['rojos']);
});
