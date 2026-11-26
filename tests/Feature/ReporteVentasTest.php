<?php

use App\Livewire\Reportes\VentasDiaModal;
use App\Models\Pago;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Venta;
use App\Services\VentasReporteService;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

/**
 * Crea dos sucursales y un usuario SuperAdmin para los reportes.
 *
 * @return array{0: Sucursal, 1: Sucursal, 2: User}
 */
function contextoReporte(): array
{
    $centro = Sucursal::create([
        'nombre_sucursal' => 'Reportes Centro',
        'direccion' => 'Av. Reportes 1',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $norte = Sucursal::create([
        'nombre_sucursal' => 'Reportes Norte',
        'direccion' => 'Av. Reportes 2',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $rol = Rol::firstOrCreate(['tipo_rol' => 'SuperAdmin'], ['descripcion' => 'Acceso total']);
    $usuario = User::factory()->create([
        'id_rol' => $rol->id,
        'id_sucursal' => $centro->id,
    ]);

    return [$centro, $norte, $usuario];
}

/**
 * Registra una venta con su pago y una fecha histórica controlada.
 */
function ventaReporte(
    Sucursal $sucursal,
    string $folio,
    float $total,
    CarbonImmutable $fecha,
    string $estado = 'Completada',
    string $metodo = 'Efectivo',
): Venta {
    $pago = Pago::create([
        'monto' => $total,
        'metodo' => $metodo,
        'estado' => 'Completado',
        'referencia' => 'REF-'.$folio,
    ]);

    $venta = Venta::create([
        'id_sucursal' => $sucursal->id,
        'folio' => $folio,
        'descuento' => 0,
        'total' => $total,
        'id_pago' => $pago->id,
        'estado' => $estado,
    ]);

    $venta->forceFill(['creado_en' => $fecha])->save();

    return $venta->fresh(['pago', 'sucursal']);
}

test('el reporte por día incluye solo el día solicitado y excluye las ventas no completadas', function () {
    $this->travelTo(CarbonImmutable::create(2026, 11, 17, 12, 0, 0));
    [$centro] = contextoReporte();

    ventaReporte($centro, 'V-DIA-1', 100, CarbonImmutable::create(2026, 11, 17, 9, 0, 0));
    ventaReporte($centro, 'V-DIA-2', 50, CarbonImmutable::create(2026, 11, 16, 10, 0, 0));
    ventaReporte($centro, 'V-DIA-CANCEL', 30, CarbonImmutable::create(2026, 11, 17, 11, 0, 0), 'Cancelada');
    ventaReporte($centro, 'V-DIA-PENDIENTE', 20, CarbonImmutable::create(2026, 11, 17, 11, 30, 0), 'Pendiente');

    $servicio = app(VentasReporteService::class);
    $reporte = $servicio->ventasPorDia('2026-11-17', $centro->id);

    expect($reporte['ventas']->pluck('folio')->all())->toBe(['V-DIA-1'])
        ->and((float) $reporte['totales']['total'])->toBe(100.0)
        ->and($reporte['etiqueta'])->toBe('Día 17/11/2026');

    expect(fn () => $servicio->ventasPorDia('2026-11-18', $centro->id))
        ->toThrow(ValidationException::class);
});

test('el reporte semanal va de lunes a domingo y la semana actual termina hoy', function () {
    $hoy = CarbonImmutable::create(2026, 11, 18, 12, 0, 0);
    $this->travelTo($hoy);
    [$centro] = contextoReporte();

    ventaReporte($centro, 'V-LUN', 10, CarbonImmutable::create(2026, 11, 16, 9, 0, 0));
    ventaReporte($centro, 'V-MIE', 20, CarbonImmutable::create(2026, 11, 18, 10, 0, 0));
    ventaReporte($centro, 'V-JUE', 40, CarbonImmutable::create(2026, 11, 19, 9, 0, 0));
    ventaReporte($centro, 'V-DOM-ANTERIOR', 5, CarbonImmutable::create(2026, 11, 15, 9, 0, 0));

    $servicio = app(VentasReporteService::class);
    $semanaActual = $hoy->format('o-\WW');
    $semanaAnterior = $hoy->startOfWeek()->subWeek()->format('o-\WW');

    $reporte = $servicio->ventasPorSemana($semanaActual, $centro->id);

    expect($reporte['ventas']->pluck('folio')->all())->toBe(['V-LUN', 'V-MIE'])
        ->and((float) $reporte['totales']['total'])->toBe(30.0)
        ->and($reporte['inicio']->format('Y-m-d'))->toBe('2026-11-16')
        ->and($reporte['fin']->format('Y-m-d'))->toBe('2026-11-18');

    $anterior = $servicio->ventasPorSemana($semanaAnterior, $centro->id);

    expect($anterior['ventas']->pluck('folio')->all())->toBe(['V-DOM-ANTERIOR']);

    expect(fn () => $servicio->ventasPorSemana($hoy->addWeek()->format('o-\WW'), $centro->id))
        ->toThrow(ValidationException::class);
});

test('el reporte mensual actual termina hoy y los meses anteriores se consultan completos', function () {
    $this->travelTo(CarbonImmutable::create(2026, 11, 17, 12, 0, 0));
    [$centro] = contextoReporte();

    ventaReporte($centro, 'V-NOV-1', 10, CarbonImmutable::create(2026, 11, 1, 9, 0, 0));
    ventaReporte($centro, 'V-NOV-17', 20, CarbonImmutable::create(2026, 11, 17, 9, 0, 0));
    ventaReporte($centro, 'V-NOV-FUTURA', 30, CarbonImmutable::create(2026, 11, 20, 9, 0, 0));
    ventaReporte($centro, 'V-OCT', 5, CarbonImmutable::create(2026, 10, 31, 9, 0, 0));
    ventaReporte($centro, 'V-DIC', 1, CarbonImmutable::create(2026, 12, 1, 9, 0, 0));

    $servicio = app(VentasReporteService::class);
    $reporte = $servicio->ventasPorMes('2026-11', $centro->id);

    expect($reporte['ventas']->pluck('folio')->all())->toBe(['V-NOV-1', 'V-NOV-17'])
        ->and((float) $reporte['totales']['total'])->toBe(30.0)
        ->and($reporte['fin']->format('Y-m-d'))->toBe('2026-11-17')
        ->and($reporte['etiqueta'])->toBe('Noviembre de 2026');

    $octubre = $servicio->ventasPorMes('2026-10', $centro->id);

    expect($octubre['ventas']->pluck('folio')->all())->toBe(['V-OCT']);

    expect(fn () => $servicio->ventasPorMes('2026-12', $centro->id))
        ->toThrow(ValidationException::class);
});

test('un usuario con sucursal fija solo consulta la suya aunque pida otra', function () {
    $this->travelTo(CarbonImmutable::create(2026, 11, 17, 12, 0, 0));
    [$centro, $norte] = contextoReporte();

    ventaReporte($centro, 'V-CENTRO', 100, CarbonImmutable::create(2026, 11, 17, 9, 0, 0));
    ventaReporte($norte, 'V-NORTE', 200, CarbonImmutable::create(2026, 11, 17, 10, 0, 0));

    $rol = Rol::create(['tipo_rol' => 'Cajero', 'descripcion' => 'Cajero']);
    $usuario = User::factory()->create([
        'id_rol' => $rol->id,
        'id_sucursal' => $centro->id,
    ]);

    $servicio = app(VentasReporteService::class);

    expect($servicio->sucursalesDisponibles($usuario)->pluck('id')->all())->toBe([$centro->id])
        ->and($servicio->puedeConsultarVariasSucursales($usuario))->toBeFalse();

    $this->actingAs($usuario)
        ->get(route('reportes.index', [
            'tipo' => 'dia',
            'fecha' => '2026-11-17',
            'sucursal' => $norte->id,
        ]))
        ->assertOk()
        ->assertSee('V-CENTRO')
        ->assertDontSee('V-NORTE');
});

test('la pantalla y el PDF comparten filtros, sucursal y totales', function () {
    $this->travelTo(CarbonImmutable::create(2026, 11, 17, 12, 0, 0));
    [$centro, $norte, $usuario] = contextoReporte();

    ventaReporte($centro, 'V-PDF-CENTRO', 125.50, CarbonImmutable::create(2026, 11, 17, 9, 0, 0));
    ventaReporte($norte, 'V-PDF-NORTE', 300, CarbonImmutable::create(2026, 11, 17, 10, 0, 0));

    $parametros = [
        'tipo' => 'dia',
        'fecha' => '2026-11-17',
        'sucursal' => $centro->id,
    ];

    $this->actingAs($usuario)
        ->get(route('reportes.index', $parametros))
        ->assertOk()
        ->assertSee('V-PDF-CENTRO')
        ->assertSee('$125.50')
        ->assertDontSee('V-PDF-NORTE');

    $this->actingAs($usuario)
        ->get(route('reportes.pdf', $parametros))
        ->assertOk()
        ->assertSee('Reporte de ventas')
        ->assertSee('Día 17/11/2026')
        ->assertSee('Reportes Centro')
        ->assertSee('V-PDF-CENTRO')
        ->assertSee('$125.50')
        ->assertSee('17/11/2026 12:00')
        ->assertDontSee('V-PDF-NORTE');
});

test('un usuario con permiso multicursal puede consolidar todas las sucursales', function () {
    $this->travelTo(CarbonImmutable::create(2026, 11, 17, 12, 0, 0));
    [$centro, $norte, $usuario] = contextoReporte();

    ventaReporte($centro, 'V-CONS-CENTRO', 100, CarbonImmutable::create(2026, 11, 17, 9, 0, 0));
    ventaReporte($norte, 'V-CONS-NORTE', 200, CarbonImmutable::create(2026, 11, 17, 10, 0, 0));

    $servicio = app(VentasReporteService::class);

    expect($servicio->puedeConsultarVariasSucursales($usuario))->toBeTrue()
        ->and($servicio->sucursalesDisponibles($usuario))->toHaveCount(2);

    $this->actingAs($usuario)
        ->get(route('reportes.index', [
            'tipo' => 'dia',
            'fecha' => '2026-11-17',
            'sucursal' => '',
        ]))
        ->assertOk()
        ->assertSee('V-CONS-CENTRO')
        ->assertSee('V-CONS-NORTE')
        ->assertSee('Todas las sucursales');
});

test('el resumen diario agrupa los totales por día e incluye días sin ventas', function () {
    $hoy = CarbonImmutable::create(2026, 11, 18, 12, 0, 0);
    $this->travelTo($hoy);
    [$centro] = contextoReporte();

    ventaReporte($centro, 'V-RES-LUN-A', 10, CarbonImmutable::create(2026, 11, 16, 9, 0, 0));
    ventaReporte($centro, 'V-RES-LUN-B', 5, CarbonImmutable::create(2026, 11, 16, 18, 0, 0));
    ventaReporte($centro, 'V-RES-MIE', 20, CarbonImmutable::create(2026, 11, 18, 10, 0, 0));

    $reporte = app(VentasReporteService::class)->ventasPorSemana($hoy->format('o-\WW'), $centro->id);
    $dias = collect($reporte['dias'])->keyBy('fecha');

    expect($reporte['dias'])->toHaveCount(3)
        ->and($dias['2026-11-16']['cantidad'])->toBe(2)
        ->and($dias['2026-11-16']['total'])->toBe(15.0)
        ->and($dias['2026-11-16']['dia_semana'])->toBe('Lunes')
        ->and($dias['2026-11-17']['cantidad'])->toBe(0)
        ->and($dias['2026-11-17']['total'])->toBe(0.0)
        ->and($dias['2026-11-18']['total'])->toBe(20.0);
});

test('la pantalla semanal muestra tarjetas por día enlazadas al modal de ventas', function () {
    $hoy = CarbonImmutable::create(2026, 11, 18, 12, 0, 0);
    $this->travelTo($hoy);
    [$centro, , $usuario] = contextoReporte();

    ventaReporte($centro, 'V-CARD-LUN', 10, CarbonImmutable::create(2026, 11, 16, 9, 0, 0));
    ventaReporte($centro, 'V-CARD-MIE', 20, CarbonImmutable::create(2026, 11, 18, 10, 0, 0));

    $this->actingAs($usuario)
        ->get(route('reportes.index', [
            'tipo' => 'semana',
            'semana' => $hoy->format('o-\WW'),
            'sucursal' => $centro->id,
        ]))
        ->assertOk()
        ->assertSee('Toca un día con ventas para ver el detalle de sus ventas.')
        ->assertSee('ver-ventas-dia', false)
        ->assertSee('Lun · 16 Nov')
        ->assertSee('$10.00')
        ->assertDontSee('V-CARD-LUN')
        ->assertDontSee('V-CARD-MIE');
});

test('la pantalla mensual también usa tarjetas por día', function () {
    $this->travelTo(CarbonImmutable::create(2026, 11, 17, 12, 0, 0));
    [$centro, , $usuario] = contextoReporte();

    ventaReporte($centro, 'V-MES-CARD-1', 33, CarbonImmutable::create(2026, 11, 1, 9, 0, 0));
    ventaReporte($centro, 'V-MES-CARD-17', 12, CarbonImmutable::create(2026, 11, 17, 9, 0, 0));

    $this->actingAs($usuario)
        ->get(route('reportes.index', [
            'tipo' => 'mes',
            'mes' => '2026-11',
            'sucursal' => $centro->id,
        ]))
        ->assertOk()
        ->assertSee('ver-ventas-dia', false)
        ->assertSee('1 Nov')
        ->assertSee('17 Nov')
        ->assertSee('$33.00')
        ->assertDontSee('V-MES-CARD-1')
        ->assertDontSee('V-MES-CARD-17');
});

test('el modal del día carga solo las ventas de esa fecha y sucursal', function () {
    $this->travelTo(CarbonImmutable::create(2026, 11, 18, 12, 0, 0));
    [$centro, $norte, $usuario] = contextoReporte();

    ventaReporte($centro, 'V-MODAL-CENTRO', 25, CarbonImmutable::create(2026, 11, 17, 9, 0, 0));
    ventaReporte($norte, 'V-MODAL-NORTE', 99, CarbonImmutable::create(2026, 11, 17, 10, 0, 0));
    ventaReporte($centro, 'V-MODAL-OTRO-DIA', 40, CarbonImmutable::create(2026, 11, 16, 11, 0, 0));

    $this->actingAs($usuario);

    Livewire::test(VentasDiaModal::class)
        ->dispatch('ver-ventas-dia', fecha: '2026-11-17', sucursal: $centro->id)
        ->assertSet('abierto', true)
        ->assertSee('V-MODAL-CENTRO')
        ->assertDontSee('V-MODAL-NORTE')
        ->assertDontSee('V-MODAL-OTRO-DIA')
        ->assertSee('$25.00')
        ->call('cerrar')
        ->assertSet('abierto', false)
        ->assertSet('ventas', []);
});

test('la gráfica del dashboard muestra las ventas de hoy de la sucursal activa', function () {
    $this->travelTo(CarbonImmutable::create(2026, 11, 17, 12, 0, 0));
    [$centro, $norte, $usuario] = contextoReporte();

    ventaReporte($centro, 'V-GRAFICA-HOY', 150.50, CarbonImmutable::create(2026, 11, 17, 9, 0, 0));
    ventaReporte($norte, 'V-GRAFICA-OTRA', 999, CarbonImmutable::create(2026, 11, 17, 10, 0, 0));

    $this->actingAs($usuario)
        ->withSession(['active_sucursal_id' => $centro->id])
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Ventas de hoy')
        ->assertSee('Día 17/11/2026')
        ->assertSee('$150.50')
        ->assertDontSee('$999.00');
});
