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
 * Lee la sección de caducidades con el nivel indicado.
 *
 * Entrada: usuario, sucursal y nivel (todos|rojo|amarillo|verde).
 * Salida: el HTML de la página de alertas.
 */
function paginaCaducidadesAlertas(User $usuario, Sucursal $sucursal, string $nivel = 'todos'): string
{
    return test()->actingAs($usuario)
        ->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('alertas.index', ['seccion' => 'caducidades', 'nivel' => $nivel]))
        ->assertOk()
        ->getContent();
}

test('el semaforo clasifica bien y excluye los lotes sin existencia', function () {
    $escenario = escenarioAlertasCaducidad();

    loteAlertasCaducidad($escenario['sucursal'], $escenario['producto'], 'L-ALERT-CAD', '2020-01-01', 5);
    loteAlertasCaducidad($escenario['sucursal'], $escenario['producto'], 'L-ALERT-CER', now()->addDays(10)->format('Y-m-d'), 5);
    loteAlertasCaducidad($escenario['sucursal'], $escenario['producto'], 'L-ALERT-PORV', now()->addDays(60)->format('Y-m-d'), 5);
    loteAlertasCaducidad($escenario['sucursal'], $escenario['producto'], 'L-ALERT-VIG', now()->addDays(200)->format('Y-m-d'), 5);
    loteAlertasCaducidad($escenario['sucursal'], $escenario['producto'], 'L-ALERT-SIN', null, 5);
    loteAlertasCaducidad($escenario['sucursal'], $escenario['producto'], 'L-ALERT-CERO', now()->addDays(5)->format('Y-m-d'), 0);
    loteAlertasCaducidad($escenario['sucursal'], $escenario['producto'], 'L-ALERT-NOREG', now()->addDays(5)->format('Y-m-d'), null);

    $todos = paginaCaducidadesAlertas($escenario['usuario'], $escenario['sucursal']);

    expect($todos)
        ->toContain('L-ALERT-CAD')
        ->toContain('L-ALERT-CER')
        ->toContain('L-ALERT-PORV')
        ->toContain('L-ALERT-VIG')
        ->toContain('L-ALERT-SIN')
        ->not->toContain('L-ALERT-CERO')
        ->not->toContain('L-ALERT-NOREG')
        ->not->toContain('Caduca en -');

    $verde = paginaCaducidadesAlertas($escenario['usuario'], $escenario['sucursal'], 'verde');
    expect($verde)
        ->toContain('L-ALERT-VIG')
        ->not->toContain('L-ALERT-CAD')
        ->not->toContain('L-ALERT-CER')
        ->not->toContain('L-ALERT-PORV');

    $rojo = paginaCaducidadesAlertas($escenario['usuario'], $escenario['sucursal'], 'rojo');
    expect($rojo)
        ->toContain('L-ALERT-CAD')
        ->toContain('L-ALERT-CER')
        ->not->toContain('L-ALERT-PORV')
        ->not->toContain('L-ALERT-SIN');

    $amarillo = paginaCaducidadesAlertas($escenario['usuario'], $escenario['sucursal'], 'amarillo');
    expect($amarillo)
        ->toContain('L-ALERT-PORV')
        ->toContain('L-ALERT-SIN')
        ->not->toContain('L-ALERT-CAD')
        ->not->toContain('L-ALERT-VIG');
});

test('el badge del sidebar cuenta los mismos rojos que la pagina', function () {
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

    $rojo = paginaCaducidadesAlertas($escenario['usuario'], $escenario['sucursal'], 'rojo');
    expect($rojo)
        ->toContain('L-ALERT-CAD')
        ->toContain('L-ALERT-CER')
        ->not->toContain('L-ALERT-CERO')
        ->not->toContain('L-ALERT-NOREG')
        ->not->toContain('L-ALERT-PORV')
        ->not->toContain('L-ALERT-VIG')
        ->not->toContain('L-ALERT-SIN');
});
