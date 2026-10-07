<?php

namespace App\Support;

use App\Models\Alerta;
use App\Models\AlertaUsuario;
use App\Models\Caja;
use App\Models\Lote;
use App\Models\Modulo;
use App\Models\Producto;
use App\Models\TipoAlerta;
use App\Models\Traspaso;
use App\Models\Venta;
use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Arma el listado único de notificaciones estilo Facebook.
 *
 * Cada aviso trae: id estable, tipo, título, detalle, url, fecha y prioridad.
 * Lo leído se persiste en alertas/alertas_usuarios (fecha_leido + estado).
 */
class AlertasFeed
{
    public const GRUPO_STOCK = 'stock';

    public const GRUPO_CADUCIDAD = 'caducidad';

    public const GRUPO_TRASPASOS = 'traspasos';

    public const GRUPO_VENTAS = 'ventas';

    /**
     * Clasificación de cada tipo de alerta en su grupo del centro de alertas.
     *
     * Stock agrupa STOCK_BAJO; Caducidad agrupa CADUCIDAD_PROXIMA y
     * PRODUCTO_CADUCADO; Traspasos agrupa PENDIENTE, RECIBIDO y RECHAZADO.
     */
    private const TIPOS_POR_GRUPO = [
        'STOCK_BAJO' => self::GRUPO_STOCK,
        'CADUCIDAD_PROXIMA' => self::GRUPO_CADUCIDAD,
        'PRODUCTO_CADUCADO' => self::GRUPO_CADUCIDAD,
        'TRASPASO_PENDIENTE' => self::GRUPO_TRASPASOS,
        'TRASPASO_RECIBIDO' => self::GRUPO_TRASPASOS,
        'TRASPASO_RECHAZADO' => self::GRUPO_TRASPASOS,
        'VENTAS_RESUMEN' => self::GRUPO_VENTAS,
    ];

    /**
     * Grupo del centro de alertas al que pertenece un tipo.
     */
    public static function grupoDeTipo(string $nombreTipo): string
    {
        return self::TIPOS_POR_GRUPO[$nombreTipo] ?? self::GRUPO_VENTAS;
    }

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
            ->with(['sucursalOrigen', 'sucursalDestino', 'detalles.lote.producto', 'solicitadoPor'])
            ->where('sucursal_b', $sucursalId)
            ->whereIn('estado', ['pendiente', 'enviado'])
            ->orderByDesc('id')
            ->limit(30)
            ->get();

        foreach ($pendientes as $traspaso) {
            $totalUnidades = (int) $traspaso->detalles->sum('cantidad');
            $nombreProducto = $traspaso->detalles->first()?->lote?->producto?->nombre_producto
                ?? ($traspaso->detalles->count() > 1 ? $traspaso->detalles->count().' lotes' : 'Lotes solicitados');

            $avisos[] = [
                'id' => "traspaso:{$traspaso->id}",
                'tipo' => 'traspasos',
                'traspaso_id' => $traspaso->id,
                'titulo' => "Traspaso T-{$traspaso->id}: {$nombreProducto}",
                'detalle' => "{$totalUnidades} uds. desde {$traspaso->sucursalOrigen?->nombre_sucursal} · pidió {$traspaso->solicitadoPor?->name}",
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
                'url' => route('alertas.index', ['sucursal' => $sucursalId, 'filtro' => 'caducidad']),
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

        $alerta = self::asegurarAlerta($sucursalId, trim($id));

        if (! $alerta) {
            return;
        }

        self::marcarLectura($alerta, $usuarioId);
    }

    /**
     * Marca una alerta como leída por su id de la tabla alertas.
     */
    public static function marcarLeidaPorAlerta(int $alertaId, ?int $usuarioId): void
    {
        if (! $usuarioId) {
            return;
        }

        $alerta = Alerta::find($alertaId);

        if (! $alerta) {
            return;
        }

        self::marcarLectura($alerta, $usuarioId);
    }

    /**
     * Crea o actualiza la fila de alertas_usuarios como LEIDA con fecha_leido.
     */
    private static function marcarLectura(Alerta $alerta, int $usuarioId): void
    {
        $lectura = AlertaUsuario::firstOrCreate(
            ['id_alerta' => $alerta->id, 'id_usuario' => $usuarioId],
            ['estado' => AlertaUsuario::ESTADO_PENDIENTE]
        );

        if ($lectura->estado !== AlertaUsuario::ESTADO_LEIDA || $lectura->fecha_leido === null) {
            $lectura->estado = AlertaUsuario::ESTADO_LEIDA;
            $lectura->fecha_leido = now();
            $lectura->save();
        }
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

        $lecturas = AlertaUsuario::query()
            ->join('alertas', 'alertas.id', '=', 'alertas_usuarios.id_alerta')
            ->where('alertas_usuarios.id_usuario', $usuarioId)
            ->where('alertas.id_sucursal', $sucursalId)
            ->where(function ($query): void {
                $query->where('alertas_usuarios.estado', AlertaUsuario::ESTADO_LEIDA)
                    ->orWhereNotNull('alertas_usuarios.fecha_leido');
            })
            ->select('alertas.entidad_tipo', 'alertas.entidad_id', 'alertas.fecha_creado', 'alertas.id_sucursal')
            ->get();

        $ids = [];

        foreach ($lecturas as $lectura) {
            $avisoId = self::avisoIdDesdeAlerta(
                (string) $lectura->entidad_tipo,
                $lectura->entidad_id === null ? null : (int) $lectura->entidad_id,
                $lectura->fecha_creado ? Carbon::parse($lectura->fecha_creado) : null,
                (int) $lectura->id_sucursal
            );

            if ($avisoId !== null) {
                $ids[] = $avisoId;
            }
        }

        return array_values(array_unique($ids));
    }

    public static function marcarTodas(int $sucursalId, array $ids, ?int $usuarioId): void
    {
        if (! $usuarioId) {
            return;
        }

        $ids = array_values(array_unique(array_filter(array_map(trim(...), $ids))));

        foreach ($ids as $id) {
            self::marcarUna($sucursalId, $id, $usuarioId);
        }
    }

    public static function noLeidasCount(?int $sucursalId, ?int $usuarioId = null): int
    {
        if (! $sucursalId) {
            return 0;
        }

        return collect(self::listado($sucursalId, $usuarioId))
            ->where('leida', false)
            ->count();
    }

    /**
     * Sincroniza el feed dinámico con la tabla alertas.
     *
     * Garantiza una fila ACTIVA por cada aviso vigente y resuelve
     * (RESUELTA) las que dejaron de estarlo: traspasos que salieron de
     * pendiente/enviado y resúmenes de ventas de días anteriores.
     */
    public static function sincronizar(int $sucursalId): void
    {
        foreach (self::items($sucursalId) as $aviso) {
            self::asegurarAlerta($sucursalId, $aviso['id']);
        }

        $pendientesIds = Traspaso::query()
            ->where('sucursal_b', $sucursalId)
            ->whereIn('estado', ['pendiente', 'enviado'])
            ->pluck('id')
            ->all();

        $tipoPendienteId = TipoAlerta::where('nombre', 'TRASPASO_PENDIENTE')->value('id');

        if ($tipoPendienteId) {
            $stale = Alerta::query()
                ->where('id_sucursal', $sucursalId)
                ->where('estado', Alerta::ESTADO_ACTIVA)
                ->where('id_tipo_alerta', $tipoPendienteId)
                ->where('entidad_tipo', 'traspaso');

            if ($pendientesIds !== []) {
                $stale->whereNotIn('entidad_id', $pendientesIds);
            }

            $stale->update(['estado' => Alerta::ESTADO_RESUELTA, 'fecha_resuelto' => now()]);
        }

        $tipoVentasId = TipoAlerta::where('nombre', 'VENTAS_RESUMEN')->value('id');

        if ($tipoVentasId) {
            Alerta::query()
                ->where('id_sucursal', $sucursalId)
                ->where('estado', Alerta::ESTADO_ACTIVA)
                ->where('id_tipo_alerta', $tipoVentasId)
                ->whereDate('fecha_creado', '<', Carbon::today())
                ->update(['estado' => Alerta::ESTADO_RESUELTA, 'fecha_resuelto' => now()]);
        }
    }

    /**
     * Listado de alertas de la sucursal leído desde el modelo Alerta.
     *
     * Cada fila trae su grupo (stock|caducidad|traspasos|ventas), su subtipo
     * (nombre del tipo_alerta) y si el usuario ya la leyó según
     * alertas_usuarios (estado LEIDA o fecha_leido). Los avisos del feed
     * conservan su presentación rica; las alertas persistidas sin aviso
     * vigente (p. ej. traspasos recibidos o stock bajo) se presentan desde
     * su tipo y entidad.
     *
     * @return array<int, array{id: string, alerta_id: int, grupo: string, subtipo: string, tipo: string, titulo: string, detalle: string, url: string, fecha: Carbon|null, prioridad: string, icono: string, leida: bool}>
     */
    public static function listado(?int $sucursalId, ?int $usuarioId): array
    {
        if (! $sucursalId) {
            return [];
        }

        self::sincronizar($sucursalId);

        $items = collect(self::items($sucursalId))->keyBy('id');

        $alertas = Alerta::query()
            ->with(['tipoAlerta', 'lecturas'])
            ->where('id_sucursal', $sucursalId)
            ->where('estado', Alerta::ESTADO_ACTIVA)
            ->orderByDesc('fecha_creado')
            ->orderByDesc('id')
            ->get();

        $delFeed = [];
        $extras = [];

        foreach ($alertas as $alerta) {
            $lectura = $usuarioId ? $alerta->lecturas->firstWhere('id_usuario', $usuarioId) : null;
            $leida = $lectura !== null && $lectura->estaLeida();

            $avisoId = self::avisoIdDesdeAlerta(
                (string) $alerta->entidad_tipo,
                $alerta->entidad_id === null ? null : (int) $alerta->entidad_id,
                $alerta->fecha_creado,
                (int) $alerta->id_sucursal
            );

            $item = ($avisoId !== null && $items->has($avisoId)) ? $items->get($avisoId) : null;
            $fila = self::presentarAlerta($alerta, $item, $leida, $sucursalId);

            if ($item !== null) {
                $delFeed[] = $fila;
            } else {
                $extras[] = $fila;
            }
        }

        $ordenFeed = array_flip($items->keys()->all());

        usort($delFeed, fn (array $a, array $b) => ($ordenFeed[$a['id']] ?? PHP_INT_MAX) <=> ($ordenFeed[$b['id']] ?? PHP_INT_MAX));

        return array_values(array_merge($delFeed, $extras));
    }

    /**
     * Presenta una alerta como fila del centro de alertas.
     */
    private static function presentarAlerta(Alerta $alerta, ?array $item, bool $leida, int $sucursalId): array
    {
        $subtipo = $alerta->tipoAlerta?->nombre ?? '';
        $grupo = self::grupoDeTipo($subtipo);

        if ($item !== null) {
            return array_merge($item, [
                'alerta_id' => $alerta->id,
                'grupo' => $grupo,
                'subtipo' => $subtipo,
                'tipo' => $grupo,
                'leida' => $leida,
            ]);
        }

        $generica = match ($alerta->entidad_tipo) {
            'traspaso' => self::presentarTraspasoGenerico($alerta, $sucursalId),
            'lote' => self::presentarLoteGenerico($alerta, $sucursalId),
            'producto' => self::presentarProductoGenerico($alerta, $sucursalId),
            default => null,
        };

        $fecha = $alerta->fecha_creado ?? now();
        $prioridad = match ($alerta->tipoAlerta?->nivel) {
            TipoAlerta::NIVEL_CRITICA, TipoAlerta::NIVEL_ADVERTENCIA => 'alta',
            default => $grupo === self::GRUPO_VENTAS ? 'baja' : 'media',
        };

        return array_merge([
            'id' => "alerta:{$alerta->id}",
            'alerta_id' => $alerta->id,
            'grupo' => $grupo,
            'subtipo' => $subtipo,
            'tipo' => $grupo,
            'titulo' => $subtipo !== '' ? ucfirst(strtolower(str_replace('_', ' ', $subtipo))) : 'Alerta',
            'detalle' => trim("{$alerta->entidad_tipo} #{$alerta->entidad_id}"),
            'mensaje' => null,
            'avatar' => '•',
            'url' => route('alertas.index', ['sucursal' => $sucursalId, 'filtro' => $grupo]),
            'fecha' => $fecha instanceof Carbon ? $fecha : Carbon::parse($fecha),
            'prioridad' => $prioridad,
            'icono' => '●',
            'leida' => $leida,
        ], array_filter($generica ?? []));
    }

    /**
     * Presenta un traspaso sin aviso vigente (recibido o rechazado).
     *
     * @return array<string, mixed>
     */
    private static function presentarTraspasoGenerico(Alerta $alerta, int $sucursalId): array
    {
        $traspaso = $alerta->entidad_id === null ? null : Traspaso::query()
            ->with(['sucursalOrigen', 'detalles.lote.producto', 'solicitadoPor'])
            ->find($alerta->entidad_id);

        $subtipo = $alerta->tipoAlerta?->nombre ?? '';
        $sufijo = match ($subtipo) {
            'TRASPASO_RECIBIDO' => ' · recibido',
            'TRASPASO_RECHAZADO' => ' · rechazado',
            default => '',
        };

        if (! $traspaso) {
            return ['detalle' => "Traspaso #{$alerta->entidad_id}{$sufijo}"];
        }

        $totalUnidades = (int) $traspaso->detalles->sum('cantidad');
        $nombreProducto = $traspaso->detalles->first()?->lote?->producto?->nombre_producto ?? 'Lotes solicitados';

        return [
            'id' => "traspaso:{$traspaso->id}",
            'titulo' => "Traspaso T-{$traspaso->id}: {$nombreProducto}{$sufijo}",
            'detalle' => "{$totalUnidades} uds. desde {$traspaso->sucursalOrigen?->nombre_sucursal} · pidió {$traspaso->solicitadoPor?->name}",
            'mensaje' => $traspaso->motivo_respuesta ?? $traspaso->mensaje,
            'avatar' => mb_strtoupper(mb_substr($traspaso->sucursalOrigen?->nombre_sucursal ?? '?', 0, 1)),
            'url' => route('alertas.index', ['sucursal' => $sucursalId, 'filtro' => 'traspasos']),
            'fecha' => $traspaso->respondido_en ?? $traspaso->creado_en ?? $alerta->fecha_creado,
            'icono' => '⇄',
            'traspaso_id' => $subtipo === 'TRASPASO_PENDIENTE' ? $traspaso->id : null,
        ];
    }

    /**
     * Presenta un lote sin aviso vigente (fuera de la ventana del feed).
     *
     * @return array<string, mixed>
     */
    private static function presentarLoteGenerico(Alerta $alerta, int $sucursalId): array
    {
        $lote = $alerta->entidad_id === null ? null : Lote::query()->with('producto')->find($alerta->entidad_id);

        if (! $lote) {
            return [];
        }

        $nombre = $lote->producto?->nombre_producto ?? 'Producto sin nombre';

        return [
            'id' => "caducidad:{$lote->id}",
            'titulo' => "Por caducar: {$nombre}",
            'detalle' => "Lote {$lote->folio}",
            'avatar' => mb_strtoupper(mb_substr($nombre, 0, 1)),
            'url' => route('alertas.index', ['sucursal' => $sucursalId, 'filtro' => 'caducidad']),
            'fecha' => $lote->fecha_de_caducidad ?? $lote->fecha_caducidad ?? $alerta->fecha_creado,
            'icono' => '●',
        ];
    }

    /**
     * Presenta un aviso de stock bajo desde su producto.
     *
     * @return array<string, mixed>
     */
    private static function presentarProductoGenerico(Alerta $alerta, int $sucursalId): array
    {
        $producto = $alerta->entidad_id === null ? null : Producto::find($alerta->entidad_id);
        $nombre = $producto?->nombre_producto ?? "Producto #{$alerta->entidad_id}";

        return [
            'titulo' => "Stock bajo: {$nombre}",
            'detalle' => $producto ? "Código {$producto->codigo_barras} · revisa existencias" : 'Revisa existencias',
            'avatar' => mb_strtoupper(mb_substr($nombre, 0, 1)),
            'url' => route('inventario.index', ['sucursal' => $sucursalId]),
            'icono' => '⚠',
        ];
    }

    /**
     * Traduce el id estable del aviso ("traspaso:5", "caducidad:3",
     * "ventas:1:2026-10-07") a su origen dinámico entidad_tipo + entidad_id.
     *
     * @return array{entidad_tipo: string, entidad_id: int|null}|null
     */
    private static function resolverAviso(string $avisoId): ?array
    {
        $partes = explode(':', $avisoId);

        if ($partes[0] === 'traspaso' && isset($partes[1]) && is_numeric($partes[1])) {
            return ['entidad_tipo' => 'traspaso', 'entidad_id' => (int) $partes[1]];
        }

        if ($partes[0] === 'caducidad' && isset($partes[1]) && is_numeric($partes[1])) {
            return ['entidad_tipo' => 'lote', 'entidad_id' => (int) $partes[1]];
        }

        if ($partes[0] === 'ventas' && isset($partes[1]) && is_numeric($partes[1])) {
            return ['entidad_tipo' => 'ventas', 'entidad_id' => (int) $partes[1]];
        }

        return null;
    }

    /**
     * Reconstruye el id estable del aviso desde una fila de alertas.
     */
    private static function avisoIdDesdeAlerta(string $entidadTipo, ?int $entidadId, ?CarbonInterface $fechaCreado, int $sucursalId): ?string
    {
        if ($entidadTipo === 'traspaso' && $entidadId !== null) {
            return "traspaso:{$entidadId}";
        }

        if ($entidadTipo === 'lote' && $entidadId !== null) {
            return "caducidad:{$entidadId}";
        }

        if ($entidadTipo === 'ventas' && $entidadId !== null) {
            $fecha = ($fechaCreado ?? Carbon::today())->format('Y-m-d');

            return "ventas:{$entidadId}:{$fecha}";
        }

        return null;
    }

    /**
     * Garantiza que exista la fila de alerta + tipo para un aviso del feed.
     */
    private static function asegurarAlerta(int $sucursalId, string $avisoId): ?Alerta
    {
        $origen = self::resolverAviso($avisoId);

        if (! $origen) {
            return null;
        }

        $nombreTipo = match ($origen['entidad_tipo']) {
            'traspaso' => self::tipoParaTraspaso($origen['entidad_id']),
            'lote' => self::tipoParaLote($origen['entidad_id']),
            'ventas' => 'VENTAS_RESUMEN',
            default => null,
        };

        if (! $nombreTipo) {
            return null;
        }

        $tipo = self::asegurarTipoAlerta($nombreTipo);

        if (! $tipo) {
            return null;
        }

        if ($origen['entidad_tipo'] === 'ventas') {
            $existente = Alerta::query()
                ->where('id_tipo_alerta', $tipo->id)
                ->where('id_sucursal', $sucursalId)
                ->where('entidad_tipo', 'ventas')
                ->where('entidad_id', $origen['entidad_id'])
                ->whereDate('fecha_creado', Carbon::today())
                ->first();

            if ($existente) {
                return $existente;
            }
        } else {
            $existente = Alerta::query()
                ->where('id_tipo_alerta', $tipo->id)
                ->where('id_sucursal', $sucursalId)
                ->where('entidad_tipo', $origen['entidad_tipo'])
                ->where('entidad_id', $origen['entidad_id'])
                ->orderByDesc('id')
                ->first();

            if ($existente) {
                if ($existente->estado !== Alerta::ESTADO_ACTIVA) {
                    $existente->estado = Alerta::ESTADO_ACTIVA;
                    $existente->fecha_resuelto = null;
                    $existente->save();
                }

                return $existente;
            }
        }

        return Alerta::create([
            'id_tipo_alerta' => $tipo->id,
            'id_sucursal' => $sucursalId,
            'entidad_tipo' => $origen['entidad_tipo'],
            'entidad_id' => $origen['entidad_id'],
            'estado' => Alerta::ESTADO_ACTIVA,
        ]);
    }

    private static function tipoParaTraspaso(?int $traspasoId): string
    {
        if ($traspasoId) {
            $estado = strtolower((string) Traspaso::whereKey($traspasoId)->value('estado'));

            if (in_array($estado, ['aceptado', 'recibido', 'aprobado', 'completado'], true)) {
                return 'TRASPASO_RECIBIDO';
            }

            if (in_array($estado, ['rechazado', 'cancelado'], true)) {
                return 'TRASPASO_RECHAZADO';
            }
        }

        return 'TRASPASO_PENDIENTE';
    }

    private static function tipoParaLote(?int $loteId): string
    {
        if ($loteId) {
            $lote = Lote::find($loteId);

            if ($lote && $lote->estaCaducado()) {
                return 'PRODUCTO_CADUCADO';
            }
        }

        return 'CADUCIDAD_PROXIMA';
    }

    /**
     * Garantiza que exista el tipo de alerta (útil en tests sin seeders).
     */
    private static function asegurarTipoAlerta(string $nombre): ?TipoAlerta
    {
        $existente = TipoAlerta::where('nombre', $nombre)->first();

        if ($existente) {
            return $existente;
        }

        $niveles = [
            'STOCK_BAJO' => TipoAlerta::NIVEL_ADVERTENCIA,
            'CADUCIDAD_PROXIMA' => TipoAlerta::NIVEL_ADVERTENCIA,
            'PRODUCTO_CADUCADO' => TipoAlerta::NIVEL_CRITICA,
            'TRASPASO_PENDIENTE' => TipoAlerta::NIVEL_ADVERTENCIA,
            'TRASPASO_RECIBIDO' => TipoAlerta::NIVEL_INFO,
            'TRASPASO_RECHAZADO' => TipoAlerta::NIVEL_ADVERTENCIA,
            'VENTAS_RESUMEN' => TipoAlerta::NIVEL_INFO,
        ];

        $modulos = [
            'STOCK_BAJO' => 'Inventario',
            'CADUCIDAD_PROXIMA' => 'Lotes y caducidades',
            'PRODUCTO_CADUCADO' => 'Lotes y caducidades',
            'TRASPASO_PENDIENTE' => 'Traspasos',
            'TRASPASO_RECIBIDO' => 'Traspasos',
            'TRASPASO_RECHAZADO' => 'Traspasos',
            'VENTAS_RESUMEN' => 'Alertas',
        ];

        $modulo = Modulo::firstOrCreate(['nombre_modulo' => $modulos[$nombre] ?? 'Alertas']);

        return TipoAlerta::create([
            'nombre' => $nombre,
            'id_modulo' => $modulo->id,
            'nivel' => $niveles[$nombre] ?? TipoAlerta::NIVEL_INFO,
            'activo' => true,
        ]);
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
