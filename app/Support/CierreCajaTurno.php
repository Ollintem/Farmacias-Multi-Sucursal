<?php

namespace App\Support;

use App\Models\CorteCaja;
use Illuminate\Support\Facades\DB;

/**
 * Cierre automático de cajas al vencer la tolerancia del turno.
 *
 * El turno termina a una hora fija (CorteCaja::HORARIOS); el cajero tiene
 * CorteCaja::TOLERANCIA_CIERRE_MINUTOS para cerrar su caja antes de que el
 * sistema la cierre sin diferencia.
 */
class CierreCajaTurno
{
    /**
     * Cierra los cortes abiertos cuya tolerancia de turno ya venció.
     *
     * Entrada: id de sucursal opcional (null procesa todas).
     * Salida: cantidad de cajas cerradas automáticamente.
     */
    public static function procesar(?int $sucursalId = null): int
    {
        $cerradas = 0;

        CorteCaja::query()
            ->where('estado', 'abierto')
            ->when($sucursalId !== null, fn ($query) => $query->where('id_sucursal', $sucursalId))
            ->get()
            ->each(function (CorteCaja $corte) use (&$cerradas): void {
                if (! $corte->toleranciaVencida()) {
                    return;
                }

                $cerrada = DB::transaction(function () use ($corte): bool {
                    $bloqueado = CorteCaja::query()->lockForUpdate()->find($corte->id);

                    return $bloqueado?->cerrarPorSistema() ?? false;
                });

                if ($cerrada) {
                    $cerradas++;
                }
            });

        return $cerradas;
    }
}
