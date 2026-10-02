<?php

namespace App\Support;

use App\Models\Lote;
use App\Models\Traspaso;
use Carbon\Carbon;

class AlertasResumen
{
    /**
     * Cuenta lo pendiente de alertas para una sucursal.
     *
     * Suma traspasos pendientes recibidos más lotes en rojo
     * (caducados o con 30 días o menos de vida).
     *
     * @return array{pendientes: int, rojos: int, total: int}
     */
    public static function counts(?int $sucursalId): array
    {
        if (! $sucursalId) {
            return ['pendientes' => 0, 'rojos' => 0, 'total' => 0];
        }

        $pendientes = Traspaso::query()
            ->where('sucursal_b', $sucursalId)
            ->whereIn('estado', ['pendiente', 'enviado'])
            ->count();

        $limite = Carbon::today()->addDays(30)->endOfDay();

        $rojos = Lote::query()
            ->where(function ($subQuery) use ($sucursalId) {
                $subQuery->whereHas('inventarios', fn ($query) => $query->where('inventario.id_sucursal', $sucursalId))
                    ->orWhereDoesntHave('inventarios');
            })
            ->whereRaw('COALESCE(fecha_de_caducidad, fecha_caducidad) IS NOT NULL')
            ->whereRaw('COALESCE(fecha_de_caducidad, fecha_caducidad) <= ?', [$limite])
            ->count();

        return ['pendientes' => $pendientes, 'rojos' => $rojos, 'total' => $pendientes + $rojos];
    }
}
