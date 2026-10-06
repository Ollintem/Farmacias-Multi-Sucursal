<?php

namespace App\Support;

use App\Models\Caja;
use App\Models\Lote;
use App\Models\NotificacionLeida;
use App\Models\Traspaso;
use App\Models\Venta;
use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Arma el listado único de notificaciones estilo Facebook.
 *
 * Cada aviso trae: id estable, tipo, título, detalle, url, fecha y prioridad.
 * Lo no leído se guarda en sesión por sucursal (sin tabla nueva).
 */
class AlertasFeed
{
    /**
     * Construye todos los avisos de la sucursal, nuevos primero.
     *
     * @return array<int, array{id: string, tipo: string, titulo: string, detalle: string, url: string, fecha: Carbon|null, prioridad: string, icono: string}>
     */
    public static function items(?int $sucursalId): array
    {
        if (! $sucursalId) {
            return [];
        }

        $avisos = [];

        $pendientes = Traspaso::query()
            ->with(['sucursalOrigen', 'producto', 'solicitadoPor'])
            ->where('sucursal_b', $sucursalId)
            ->whereIn('estado', ['pendiente', 'enviado'])
            ->orderByDesc('id')
            ->limit(30)
            ->get();

        foreach ($pendientes as $traspaso) {
            $avisos[] = [
                'id' => "traspaso:{$traspaso->id}",
                'tipo' => 'traspasos',
                'traspaso_id' => $traspaso->id,
                'titulo' => "Traspaso T-{$traspaso->id}: {$traspaso->producto?->nombre_producto}",
                'detalle' => "{$traspaso->cantidad} uds. desde {$traspaso->sucursalOrigen?->nombre_sucursal} · pidió {$traspaso->solicitadoPor?->name}",
                'mensaje' => $traspaso->mensaje,
                'avatar' => mb_strtoupper(mb_substr($traspaso->sucursalOrigen?->nombre_sucursal ?? '?', 0, 1)),
                'url' => route('alertas.index', ['sucursal' => $sucursalId, 'filtro' => 'traspasos']),
                'fecha' => $traspaso->creado_en ? Carbon::parse($traspaso->creado_en) : null,
                'prioridad' => 'alta',
                'icono' => '⇄',
            ];
        }

        $limiteProximo = Carbon::today()->addDays(90)->endOfDay();

        $proximos = Lote::query()
            ->with(['producto', 'inventarios.sucursal'])
            ->where(function ($sub) use ($sucursalId) {
                $sub->whereHas('inventarios', fn ($q) => $q->where('inventario.id_sucursal', $sucursalId))
                    ->orWhereDoesntHave('inventarios');
            })
            ->whereRaw('COALESCE(fecha_de_caducidad, fecha_caducidad) IS NOT NULL')
            ->whereRaw('COALESCE(fecha_de_caducidad, fecha_caducidad) <= ?', [$limiteProximo])
            ->orderByRaw('COALESCE(fecha_de_caducidad, fecha_caducidad) ASC')
            ->limit(30)
            ->get();

        foreach ($proximos as $lote) {
            $fechaRaw = $lote->fecha_de_caducidad ?? $lote->fecha_caducidad;
            $fecha = $fechaRaw ? Carbon::parse($fechaRaw) : null;
            $nombre = $lote->producto?->nombre_producto ?? 'Producto sin nombre';
            $hoy = Carbon::today();

            if ($fecha && $fecha->lt($hoy)) {
                $etiqueta = 'Caducado';
                $prioridad = 'alta';
            } elseif ($fecha && (int) $hoy->diffInDays($fecha) <= 30) {
                $dias = (int) $hoy->diffInDays($fecha);
                $etiqueta = "Caduca en {$dias} días · {$fecha->format('Y-m-d')}";
                $prioridad = 'alta';
            } else {
                $dias = (int) $hoy->diffInDays($fecha ?? $hoy);
                $etiqueta = "Media vida · {$dias} días · {$fecha?->format('Y-m-d')}";
                $prioridad = 'media';
            }

            $avisos[] = [
                'id' => "caducidad:{$lote->id}",
                'tipo' => 'caducidades',
                'titulo' => "Por caducar: {$nombre}",
                'detalle' => "Lote {$lote->folio} · {$etiqueta}",
                'mensaje' => null,
                'avatar' => mb_strtoupper(mb_substr($nombre, 0, 1)),
                'url' => route('alertas.index', ['sucursal' => $sucursalId, 'filtro' => 'caducidades']),
                'fecha' => $fecha,
                'prioridad' => $prioridad,
                'icono' => '●',
            ];
        }

        $resumenVentas = self::resumenVentasHoy($sucursalId);

        if ($resumenVentas['total_ventas'] > 0) {
            $avisos[] = [
                'id' => "ventas:{$sucursalId}:".Carbon::today()->format('Y-m-d'),
                'tipo' => 'ventas',
                'titulo' => "Hoy: {$resumenVentas['total_ventas']} ventas · $".number_format($resumenVentas['monto_total'], 2),
                'detalle' => 'Ticket promedio $'.number_format($resumenVentas['ticket_promedio'], 2).' · '.($resumenVentas['ultima_venta']?->folio ?? 'sin folio'),
                'mensaje' => null,
                'avatar' => '$',
                'url' => route('alertas.index', ['sucursal' => $sucursalId, 'filtro' => 'ventas']),
                'fecha' => $resumenVentas['ultima_venta']?->creado_en ? Carbon::parse($resumenVentas['ultima_venta']->creado_en) : Carbon::today(),
                'prioridad' => 'baja',
                'icono' => '▣',
            ];
        }

        usort($avisos, function (array $a, array $b) {
            $orden = ['alta' => 0, 'media' => 1, 'baja' => 2];

            if ($orden[$a['prioridad']] !== $orden[$b['prioridad']]) {
                return $orden[$a['prioridad']] <=> $orden[$b['prioridad']];
            }

            $fechaA = $a['fecha']?->timestamp ?? 0;
            $fechaB = $b['fecha']?->timestamp ?? 0;

            return $fechaB <=> $fechaA;
        });

        return array_values($avisos);
    }

    /**
     * Marca un solo aviso como leído (menú ••• de cada fila). Persiste en BD.
     */
    public static function marcarUna(int $sucursalId, string $id, ?int $usuarioId): void
    {
        if (! $usuarioId || trim($id) === '') {
            return;
        }

        NotificacionLeida::firstOrCreate([
            'id_usuario' => $usuarioId,
            'id_sucursal' => $sucursalId,
            'aviso_id' => $id,
        ]);
    }

    /**
     * Tiempo corto estilo Facebook: "5 min", "4 h", "3 d" o la fecha.
     */
    public static function tiempoCorto(?CarbonInterface $fecha): string
    {
        if (! $fecha) {
            return 'reciente';
        }

        $minutos = (int) $fecha->diffInMinutes(now());

        if ($minutos < 1) {
            return 'ahora mismo';
        }

        if ($minutos < 60) {
            return "{$minutos} min";
        }

        $horas = (int) $fecha->diffInHours(now());

        if ($horas < 24) {
            return "{$horas} h";
        }

        $dias = (int) $fecha->diffInDays(now());

        if ($dias < 7) {
            return "{$dias} d";
        }

        return $fecha->format('d M Y');
    }

    /** @return array<int, string> */
    public static function leidas(int $sucursalId, ?int $usuarioId): array
    {
        if (! $usuarioId) {
            return [];
        }

        return NotificacionLeida::query()
            ->where('id_usuario', $usuarioId)
            ->where('id_sucursal', $sucursalId)
            ->pluck('aviso_id')
            ->all();
    }

    public static function marcarTodas(int $sucursalId, array $ids, ?int $usuarioId): void
    {
        if (! $usuarioId) {
            return;
        }

        $ids = array_values(array_unique(array_filter(array_map(trim(...), $ids))));

        foreach ($ids as $id) {
            NotificacionLeida::firstOrCreate([
                'id_usuario' => $usuarioId,
                'id_sucursal' => $sucursalId,
                'aviso_id' => $id,
            ]);
        }
    }

    public static function noLeidasCount(?int $sucursalId, ?int $usuarioId = null): int
    {
        if (! $sucursalId) {
            return 0;
        }

        $leidas = self::leidas($sucursalId, $usuarioId);

        return collect(self::items($sucursalId))
            ->reject(fn (array $aviso) => in_array($aviso['id'], $leidas, true))
            ->count();
    }

    private static function resumenVentasHoy(int $sucursalId): array
    {
        $cajaIds = Caja::where('id_sucursal', $sucursalId)->pluck('id');

        $ventasHoy = Venta::query()
            ->whereIn('id_caja', $cajaIds)
            ->whereDate('creado_en', Carbon::today())
            ->orderByDesc('creado_en')
            ->get();

        $total = (int) $ventasHoy->count();
        $monto = (float) $ventasHoy->sum('total');

        return [
            'total_ventas' => $total,
            'monto_total' => round($monto, 2),
            'ticket_promedio' => $total > 0 ? round($monto / $total, 2) : 0.0,
            'ultima_venta' => $ventasHoy->first(),
        ];
    }
}
