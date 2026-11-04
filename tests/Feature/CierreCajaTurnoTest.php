<?php

use App\Livewire\AvisoTurnoCaja;
use App\Livewire\PuntoDeVenta;
use App\Models\Alerta;
use App\Models\CorteCaja;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;
use App\Support\AlertasFeed;
use App\Support\CierreCajaTurno;
use Livewire\Livewire;

/**
 * Contexto mínimo: sucursal, cajero SuperAdmin y un corte en el turno dado.
 *
 * @return array{0: Sucursal, 1: User, 2: CorteCaja}
 */
function contextoCierreTurno(string $turno = 'Matutino', ?DateTimeInterface $inicio = null): array
{
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Cierre Turno',
        'direccion' => 'Av. Turno 100',
        'hora_apertura' => '08:00',
        'hora_cierre' => '22:00',
    ]);

    $rol = Rol::firstOrCreate(
        ['tipo_rol' => 'SuperAdmin'],
        ['descripcion' => 'Acceso total']
    );

    $user = User::factory()->create([
        'id_sucursal' => $sucursal->id,
        'id_rol' => $rol->id,
    ]);

    $corte = CorteCaja::create([
        'id_sucursal' => $sucursal->id,
        'id_usuario' => $user->id,
        'caja' => 'Caja 1',
        'turno' => $turno,
        'efectivo_inicial' => 100,
        'efectivo_declarado' => 0,
        'efectivo_esperado' => 100,
        'diferencia' => 0,
        'fecha_inicio' => $inicio ?? now(),
        'estado' => 'abierto',
    ]);

    test()->actingAs($user);
    session(['active_sucursal_id' => $sucursal->id]);

    return [$sucursal, $user, $corte];
}

test('calcula el fin de turno y sus ventanas de aviso y tolerancia', function () {
    $inicio = now()->copy()->setTime(9, 0);
    [, , $corte] = contextoCierreTurno('Matutino', $inicio);

    expect($corte->finDeTurno()?->format('H:i'))->toBe('15:00');

    $this->travelTo($inicio->copy()->setTime(14, 44));
    expect($corte->estaPorTerminarTurno())->toBeFalse()
        ->and($corte->turnoVencido())->toBeFalse();

    $this->travelTo($inicio->copy()->setTime(14, 50));
    expect($corte->estaPorTerminarTurno())->toBeTrue()
        ->and($corte->turnoVencido())->toBeFalse()
        ->and($corte->toleranciaVencida())->toBeFalse();

    $this->travelTo($inicio->copy()->setTime(15, 5));
    expect($corte->turnoVencido())->toBeTrue()
        ->and($corte->toleranciaVencida())->toBeFalse();

    $this->travelTo($inicio->copy()->setTime(15, 10));
    expect($corte->toleranciaVencida())->toBeTrue();
});

test('un corte abierto despues del cierre de su turno no entra en la ventana', function () {
    $inicio = now()->copy()->setTime(22, 30);
    [, , $corte] = contextoCierreTurno('Vespertino', $inicio);

    expect($corte->finDeTurno())->toBeNull()
        ->and($corte->estaPorTerminarTurno())->toBeFalse()
        ->and($corte->turnoVencido())->toBeFalse()
        ->and($corte->toleranciaVencida())->toBeFalse();
});

test('el cajero recibe la notificacion de turno por terminar y nadie mas la ve', function () {
    $inicio = now()->copy()->setTime(9, 0);
    [$sucursal, $user, $corte] = contextoCierreTurno('Matutino', $inicio);
    $otro = User::factory()->create(['id_sucursal' => $sucursal->id]);

    $this->travelTo($inicio->copy()->setTime(14, 50));

    $avisos = AlertasFeed::listado($sucursal->id, $user->id);
    $aviso = collect($avisos)->firstWhere('id', "caja:{$corte->id}");

    expect($aviso)->not->toBeNull()
        ->and($aviso['grupo'])->toBe('caja')
        ->and($aviso['titulo'])->toContain('turno por terminar')
        ->and($aviso['detalle'])->toContain('15:00');

    expect(collect(AlertasFeed::listado($sucursal->id, $otro->id))->firstWhere('id', "caja:{$corte->id}"))
        ->toBeNull();

    expect(Alerta::query()
        ->where('entidad_tipo', 'corte_caja')
        ->where('entidad_id', $corte->id)
        ->where('estado', Alerta::ESTADO_ACTIVA)
        ->exists())->toBeTrue();
});

test('el aviso de caja no aparece antes de la ventana de 15 minutos', function () {
    $inicio = now()->copy()->setTime(9, 0);
    [$sucursal, $user, $corte] = contextoCierreTurno('Matutino', $inicio);

    $this->travelTo($inicio->copy()->setTime(14, 30));

    expect(collect(AlertasFeed::listado($sucursal->id, $user->id))->firstWhere('id', "caja:{$corte->id}"))
        ->toBeNull();
});

test('el sistema cierra la caja al vencer los 10 minutos de tolerancia', function () {
    $inicio = now()->copy()->setTime(9, 0);
    [$sucursal, , $corte] = contextoCierreTurno('Matutino', $inicio);

    $this->travelTo($inicio->copy()->setTime(15, 9));
    expect(CierreCajaTurno::procesar($sucursal->id))->toBe(0)
        ->and($corte->fresh()->estado)->toBe('abierto');

    $this->travelTo($inicio->copy()->setTime(15, 10));
    expect(CierreCajaTurno::procesar($sucursal->id))->toBe(1);

    $corte->refresh();

    expect($corte->estado)->toBe('cerrado')
        ->and($corte->cierre_automatico)->toBeTrue()
        ->and((float) $corte->diferencia)->toBe(0.0)
        ->and((float) $corte->efectivo_declarado)->toBe((float) $corte->efectivo_esperado)
        ->and($corte->fecha_cierre)->not->toBeNull();

    // Es idempotente: un segundo intento ya no cierra nada.
    expect(CierreCajaTurno::procesar($sucursal->id))->toBe(0);
});

test('el aviso de turno cierra la caja vencida al refrescarse', function () {
    $inicio = now()->copy()->setTime(9, 0);
    [, , $corte] = contextoCierreTurno('Matutino', $inicio);

    $this->travelTo($inicio->copy()->setTime(15, 10));

    Livewire::test(AvisoTurnoCaja::class)
        ->call('verificar')
        ->assertDispatched('pos-error');

    expect($corte->fresh()->estado)->toBe('cerrado')
        ->and($corte->fresh()->cierre_automatico)->toBeTrue();
});

test('muestra el aviso de turno por terminar y permite pedir el cierre', function () {
    $inicio = now()->copy()->setTime(9, 0);
    contextoCierreTurno('Matutino', $inicio);

    $this->travelTo($inicio->copy()->setTime(14, 50));

    Livewire::test(AvisoTurnoCaja::class)
        ->assertSee('Tu turno termina en 10 min')
        ->assertSee('Cerrar mi caja')
        ->call('pedirCierre')
        ->assertDispatched('pos-cerrar-caja');
});

test('el comando programado cierra las cajas con tolerancia vencida', function () {
    $inicio = now()->copy()->setTime(9, 0);
    [, , $corte] = contextoCierreTurno('Matutino', $inicio);

    $this->travelTo($inicio->copy()->setTime(15, 11));

    $this->artisan('cajas:cerrar-vencidas')->assertExitCode(0);

    expect($corte->fresh()->estado)->toBe('cerrado')
        ->and($corte->fresh()->cierre_automatico)->toBeTrue();
});

test('el POS muestra el cierre de caja y regresa al punto de venta', function () {
    $inicio = now()->copy()->setTime(9, 0);
    [, $user, $corte] = contextoCierreTurno('Matutino', $inicio);

    Livewire::test(PuntoDeVenta::class)
        ->assertSee('Cerrar caja')
        ->call('abrirModalCierre')
        ->assertSet('cerrandoCaja', true)
        ->assertSee('Efectivo esperado');

    $this->actingAs($user)
        ->post(route('caja.cerrar', $corte), [
            'efectivo_declarado' => 500,
            'redirect_to' => 'punto-venta',
        ])
        ->assertRedirect(route('punto-venta.index'))
        ->assertSessionHas('success');

    $corte->refresh();

    expect($corte->estado)->toBe('cerrado')
        ->and($corte->cierre_automatico)->toBeFalse()
        ->and((float) $corte->efectivo_declarado)->toBe(500.0)
        ->and((float) $corte->diferencia)->toBe(400.0);
});

test('el POS y la vista de caja muestran el cierre de turno', function () {
    $inicio = now()->copy()->setTime(9, 0);
    [$sucursal, $user] = contextoCierreTurno('Matutino', $inicio);

    $this->travelTo($inicio->copy()->setTime(14, 50));

    $this->actingAs($user)
        ->get(route('punto-venta.index'))
        ->assertOk()
        ->assertSee('Cerrar caja')
        ->assertSee('Tu turno termina en 10 min');

    $this->actingAs($user)
        ->get(route('caja.index', ['sucursal' => $sucursal->id]))
        ->assertOk()
        ->assertSee('El turno termina a las 15:00');
});

test('el aviso de caja desaparece al cerrar el corte', function () {
    $inicio = now()->copy()->setTime(9, 0);
    [$sucursal, $user, $corte] = contextoCierreTurno('Matutino', $inicio);

    $this->travelTo($inicio->copy()->setTime(14, 50));
    expect(collect(AlertasFeed::listado($sucursal->id, $user->id))->firstWhere('id', "caja:{$corte->id}"))
        ->not->toBeNull();

    $corte->update(['estado' => 'cerrado', 'fecha_cierre' => now()]);

    $avisos = collect(AlertasFeed::listado($sucursal->id, $user->id));

    expect($avisos->firstWhere('id', "caja:{$corte->id}"))->toBeNull()
        ->and(Alerta::query()
            ->where('entidad_tipo', 'corte_caja')
            ->where('entidad_id', $corte->id)
            ->where('estado', Alerta::ESTADO_ACTIVA)
            ->exists())->toBeFalse();
});
