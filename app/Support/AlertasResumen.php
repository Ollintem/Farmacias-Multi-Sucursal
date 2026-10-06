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
     * Suma traspasos pendientes recibidos más lotes con existencia en rojo
     * (caducados o con 30 días o menos de vida). Los lotes sin existencia
     * se excluyen: ya no hay riesgo de caducidad que mitigar.
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
            ->whereHas('inventarios', function ($query) use ($sucursalId) {
                $query->where('inventario.id_sucursal', $sucursalId)
                    ->where('stock', '>', 0);
            })
            ->whereRaw('COALESCE(fecha_de_caducidad, fecha_caducidad) IS NOT NULL')
            ->whereRaw('COALESCE(fecha_de_caducidad, fecha_caducidad) <= ?', [$limite])
            ->count();

        return ['pendientes' => $pendientes, 'rojos' => $rojos, 'total' => $pendientes + $rojos];
    }
}
