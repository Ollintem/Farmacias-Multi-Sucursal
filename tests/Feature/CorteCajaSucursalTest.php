<?php

use App\Models\CorteCaja;
use App\Models\Pago;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

function contextoCorteCaja(): array
{
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Cortes',
        'direccion' => 'Av. Cortes 1',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $usuario = User::factory()->create(['id_sucursal' => $sucursal->id]);

    return [$sucursal, $usuario];
}

test('el corte de caja se liga directamente a la sucursal y conserva al usuario', function () {
    [$sucursal, $usuario] = contextoCorteCaja();

    $corte = CorteCaja::create([
        'id_sucursal' => $sucursal->id,
        'id_usuario' => $usuario->id,
        'turno' => 'matutino',
        'efectivo_inicial' => 500,
        'efectivo_declarado' => 1800,
        'efectivo_esperado' => 1750,
        'diferencia' => 50,
        'fecha_inicio' => now(),
        'fecha_cierre' => now(),
        'estado' => 'cerrado',
    ]);

    expect($corte->sucursal->is($sucursal))->toBeTrue()
        ->and($corte->usuario->is($usuario))->toBeTrue()
        ->and($sucursal->cortesCaja()->count())->toBe(1)
        ->and((float) $corte->fresh()->diferencia)->toBe(50.0);
});

test('la venta se liga a la sucursal y ya no existen cajas ni id_caja', function () {
    [$sucursal] = contextoCorteCaja();

    $pago = Pago::create([
        'monto' => 100,
        'metodo' => 'Efectivo',
        'estado' => 'Completado',
        'referencia' => 'REF-CORTE',
    ]);

    $venta = Venta::create([
        'id_sucursal' => $sucursal->id,
        'folio' => 'V-CORTE-1',
        'descuento' => 0,
        'total' => 100,
        'id_pago' => $pago->id,
        'estado' => 'Completada',
    ]);

    expect($venta->sucursal->is($sucursal))->toBeTrue()
        ->and($sucursal->ventas()->count())->toBe(1)
        ->and(Schema::hasTable('cajas'))->toBeFalse()
        ->and(Schema::hasColumn('cortes_caja', 'id_caja'))->toBeFalse()
        ->and(Schema::hasColumn('ventas', 'id_caja'))->toBeFalse()
        ->and(Schema::hasColumn('cortes_caja', 'id_sucursal'))->toBeTrue()
        ->and(Schema::hasColumn('ventas', 'id_sucursal'))->toBeTrue();
});
