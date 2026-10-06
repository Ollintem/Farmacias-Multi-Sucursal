<?php

use App\Support\EstadoCaducidad;
use Carbon\Carbon;

test('sin fecha queda como amarillo', function () {
    $estado = EstadoCaducidad::clasificar(null);

    expect($estado)->toBe(EstadoCaducidad::SinFecha)
        ->and($estado->estadoLotes())->toBe('Sin fecha')
        ->and($estado->nivelAlertas())->toBe('amarillo')
        ->and($estado->etiquetaAlertas(EstadoCaducidad::diasRestantes(null)))->toBe('Sin fecha')
        ->and($estado->esRojo())->toBeFalse();
});

test('ayer es caducado con dias negativos', function () {
    $ayer = Carbon::today()->subDay();
    $estado = EstadoCaducidad::clasificar($ayer);

    expect($estado)->toBe(EstadoCaducidad::Caducado)
        ->and($estado->estadoLotes())->toBe('Caducado')
        ->and($estado->claseLotes())->toBe('expired')
        ->and($estado->nivelAlertas())->toBe('rojo')
        ->and($estado->esRojo())->toBeTrue()
        ->and(EstadoCaducidad::diasRestantes($ayer))->toBe(-1)
        ->and($estado->etiquetaAlertas(-1))->toBe('Caducado');
});

test('hoy es critico y no caducado', function () {
    $hoy = Carbon::today();
    $estado = EstadoCaducidad::clasificar($hoy);

    expect($estado)->toBe(EstadoCaducidad::Critico)
        ->and(EstadoCaducidad::diasRestantes($hoy))->toBe(0)
        ->and($estado->etiquetaAlertas(0))->toBe('Caduca en 0 días')
        ->and($estado->estadoLotes())->toBe('Caduca < 30 días')
        ->and($estado->esRojo())->toBeTrue();
});

test('respeta los limites de 30 y 90 dias', function () {
    expect(EstadoCaducidad::clasificar(Carbon::today()->addDays(30)))->toBe(EstadoCaducidad::Critico)
        ->and(EstadoCaducidad::clasificar(Carbon::today()->addDays(31)))->toBe(EstadoCaducidad::PorVencer)
        ->and(EstadoCaducidad::clasificar(Carbon::today()->addDays(90)))->toBe(EstadoCaducidad::PorVencer)
        ->and(EstadoCaducidad::clasificar(Carbon::today()->addDays(91)))->toBe(EstadoCaducidad::Vigente);
});

test('mapea estados a las tres superficies', function () {
    expect(EstadoCaducidad::Critico->nivelAlertas())->toBe('rojo')
        ->and(EstadoCaducidad::Critico->claseLotes())->toBe('danger')
        ->and(EstadoCaducidad::PorVencer->nivelAlertas())->toBe('amarillo')
        ->and(EstadoCaducidad::PorVencer->claseLotes())->toBe('warning')
        ->and(EstadoCaducidad::PorVencer->etiquetaAlertas(45))->toBe('Media vida · 45 días')
        ->and(EstadoCaducidad::Vigente->nivelAlertas())->toBe('verde')
        ->and(EstadoCaducidad::Vigente->claseLotes())->toBe('vigente')
        ->and(EstadoCaducidad::Vigente->etiquetaAlertas(200))->toBe('Vigente · 200 días')
        ->and(EstadoCaducidad::Vigente->esRojo())->toBeFalse();
});
