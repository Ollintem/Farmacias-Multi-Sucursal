<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * Clasificación única de la caducidad de un lote.
 *
 * La consumen LotesController (estado en tabla y tarjetas),
 * AlertasController (semáforo rojo/amarillo/verde) y
 * AlertasResumen (badge del sidebar, vía su test de paridad)
 * para que las tres superficies nunca se desincronicen.
 */
enum EstadoCaducidad: string
{
    case SinFecha = 'sin_fecha';
    case Caducado = 'caducado';
    case Critico = 'critico';
    case PorVencer = 'por_vencer';
    case Vigente = 'vigente';

    /**
     * Clasifica la fecha de caducidad de un lote respecto a hoy.
     *
     * Entrada: fecha de caducidad (null cuando el lote no tiene).
     * Salida: estado correspondiente. Hoy cuenta como crítico
     * («caduca hoy») y solo lo estrictamente anterior a hoy es caducado.
     */
    public static function clasificar(?Carbon $fecha): self
    {
        if ($fecha === null) {
            return self::SinFecha;
        }

        if ($fecha->copy()->startOfDay()->lt(Carbon::today())) {
            return self::Caducado;
        }

        $dias = self::diasRestantes($fecha);

        if ($dias <= 30) {
            return self::Critico;
        }

        if ($dias <= 90) {
            return self::PorVencer;
        }

        return self::Vigente;
    }

    /**
     * Días enteros desde hoy hasta la fecha (negativos si ya pasó).
     *
     * Entrada: fecha de caducidad (null cuando el lote no tiene).
     * Salida: entero con signo o null si no hay fecha.
     */
    public static function diasRestantes(?Carbon $fecha): ?int
    {
        if ($fecha === null) {
            return null;
        }

        return (int) round(Carbon::today()->diffInDays($fecha->copy()->startOfDay()));
    }

    /**
     * Etiqueta que muestra la página de Lotes.
     *
     * Entrada: ninguna.
     * Salida: texto del badge de estado en lotes/index.
     */
    public function estadoLotes(): string
    {
        return match ($this) {
            self::SinFecha => 'Sin fecha',
            self::Caducado => 'Caducado',
            self::Critico => 'Caduca < 30 días',
            self::PorVencer => 'Caduca < 90 días',
            self::Vigente => 'Vigente',
        };
    }

    /**
     * Clase CSS del badge de estado en la página de Lotes.
     *
     * Entrada: ninguna.
     * Salida: clase usada por `.status-badge`.
     */
    public function claseLotes(): string
    {
        return match ($this) {
            self::SinFecha => 'warning',
            self::Caducado => 'expired',
            self::Critico => 'danger',
            self::PorVencer => 'warning',
            self::Vigente => 'vigente',
        };
    }

    /**
     * Nivel del semáforo en la página de Alertas.
     *
     * Entrada: ninguna.
     * Salida: rojo | amarillo | verde.
     */
    public function nivelAlertas(): string
    {
        return match ($this) {
            self::Caducado, self::Critico => 'rojo',
            self::SinFecha, self::PorVencer => 'amarillo',
            self::Vigente => 'verde',
        };
    }

    /**
     * Etiqueta con días que muestra la página de Alertas.
     *
     * Entrada: días restantes calculados con diasRestantes().
     * Salida: texto del badge del semáforo (sin días cuando no aplican).
     */
    public function etiquetaAlertas(?int $dias): string
    {
        return match ($this) {
            self::SinFecha => 'Sin fecha',
            self::Caducado => 'Caducado',
            self::Critico => "Caduca en {$dias} días",
            self::PorVencer => "Media vida · {$dias} días",
            self::Vigente => "Vigente · {$dias} días",
        };
    }

    /**
     * Indica si el estado cuenta como rojo en el badge del sidebar.
     *
     * Entrada: ninguna.
     * Salida: true para caducados y críticos (≤ 30 días).
     */
    public function esRojo(): bool
    {
        return match ($this) {
            self::Caducado, self::Critico => true,
            default => false,
        };
    }
}
