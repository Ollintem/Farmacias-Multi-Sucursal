<?php

namespace App\Support;

use App\Models\Traspaso;
use App\Services\InventarioService;

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
            ->where(function ($query) use ($sucursalId): void {
                $query->where('sucursal_a', $sucursalId)
                    ->orWhere('sucursal_b', $sucursalId);
            })
            ->whereIn('estado', ['pendiente', 'enviado'])
            ->count();

        $rojos = app(InventarioService::class)
            ->lotesProximosACaducar($sucursalId, 30)
            ->count();

        return ['pendientes' => $pendientes, 'rojos' => $rojos, 'total' => $pendientes + $rojos];
    }
}
