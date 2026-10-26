<?php

namespace App\Livewire;

use App\Models\Sucursal;
use App\Support\AlertasFeed;
use App\Support\AlertasResumen;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Notificaciones')]
class CentroNotificaciones extends Component
{
    public string $filtro = 'todas';

    public string $nivel = 'todos';

    public string $busqueda = '';

    public ?int $selectedSucursalId = null;

    public bool $mostrandoDetalle = false;

    public ?string $detalleId = null;

    /** @var array<string, mixed> */
    public array $detalle = [];

    public string $motivoRechazo = '';

    public ?int $traspasoRechazoId = null;

    public bool $listadoAbierto = true;

    public function mount(?int $sucursal = null, ?string $filtro = null, ?string $nivel = null): void
    {
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        $usuario = auth()->user();
        $primera = $sucursales->first();
        $sucursalQuery = request()->query('sucursal');
        $id = (int) (session('active_sucursal_id') ?? $sucursalQuery ?? $sucursal ?? ($usuario?->id_sucursal) ?? ($primera?->id ?? 0));
        $this->selectedSucursalId = $id > 0 ? $id : ($primera?->id);
        if ($this->selectedSucursalId) {
            session(['active_sucursal_id' => $this->selectedSucursalId]);
        }

        $f = strtolower(trim((string) ($filtro ?? request()->query('filtro', ''))));
        if ($f === '') {
            $seccion = strtolower(trim((string) request()->query('seccion', '')));
            $f = match ($seccion) {
                'traspasos' => 'traspasos',
                'caducidades', 'caducidad' => 'caducidad',
                'stock' => 'stock',
                'ventas' => 'ventas',
                default => 'todas',
            };
        }
        $f = match ($f) {
            'caducidades' => 'caducidad',
            'traspaso' => 'traspasos',
            default => $f,
        };
        $this->filtro = in_array($f, ['todas', 'no_leidas', 'stock', 'caducidad', 'traspasos', 'ventas'], true) ? $f : 'todas';

        $n = strtolower(trim((string) ($nivel ?? request()->query('nivel', 'todos'))));
        $this->nivel = in_array($n, ['todos', 'rojo', 'amarillo', 'verde'], true) ? $n : 'todos';

        // Apertura directa desde ?abrir=id (campana fuera de /alertas)
        $abrir = trim((string) request()->query('abrir', ''));
        if ($abrir !== '') {
            $this->detalleId = $abrir;
            $this->mostrandoDetalle = true;
            $this->cargarDetalle($abrir);
        }
    }

    public function updatedSelectedSucursalId(mixed $value): void
    {
        $id = (int) $value;
        if ($id > 0) {
            session(['active_sucursal_id' => $id]);
            $this->dispatch('sucursal-cambiada', id: $id);
        }
    }

    public function setFiltro(string $filtro): void
    {
        $filtro = strtolower(trim($filtro));
        $this->filtro = in_array($filtro, ['todas', 'no_leidas', 'stock', 'caducidad', 'traspasos', 'ventas'], true) ? $filtro : 'todas';
        if ($this->filtro !== 'caducidad') {
            $this->nivel = 'todos';
        }
    }

    public function setNivel(string $nivel): void
    {
        $nivel = strtolower(trim($nivel));
        $this->nivel = in_array($nivel, ['todos', 'rojo', 'amarillo', 'verde'], true) ? $nivel : 'todos';
        $this->filtro = 'caducidad';
    }

    public function marcarLeida(string $id): void
    {
        $sucursalId = (int) ($this->selectedSucursalId ?? 0);
        $usuarioId = auth()->id();
        if ($sucursalId && $id !== '') {
            if (ctype_digit($id)) {
                AlertasFeed::marcarLeidaPorAlerta((int) $id, $usuarioId);
            } else {
                AlertasFeed::marcarUna($sucursalId, $id, $usuarioId);
            }
            if ($this->detalleId === $id) {
                $this->cargarDetalle($id);
            }
            $this->dispatch('notificacion-actualizada', id: $id, leida: true);
        }
    }

    public function marcarNoLeida(string $id): void
    {
        $sucursalId = (int) ($this->selectedSucursalId ?? 0);
        $usuarioId = auth()->id();
        if ($sucursalId && $id !== '') {
            AlertasFeed::desmarcarUna($sucursalId, $id, $usuarioId);
            if ($this->detalleId === $id) {
                $this->cargarDetalle($id);
            }
            $this->dispatch('notificacion-actualizada', id: $id, leida: false);
        }
    }

    public function marcarTodasLeidas(): void
    {
        $sucursalId = (int) ($this->selectedSucursalId ?? 0);
        $usuarioId = auth()->id();
        if (! $sucursalId) {
            return;
        }
        foreach (AlertasFeed::listado($sucursalId, $usuarioId) as $aviso) {
            $alertaId = $aviso['alerta_id'] ?? null;
            if ($alertaId !== null) {
                AlertasFeed::marcarLeidaPorAlerta((int) $alertaId, $usuarioId);
            }
        }
        $this->dispatch('notificaciones-todas-leidas');
    }

    public function abrirDetalle(string $id): void
    {
        $this->detalleId = $id;
        $this->cargarDetalle($id);
        $this->mostrandoDetalle = true;
    }

    public function cerrarDetalle(): void
    {
        $this->mostrandoDetalle = false;
        $this->detalleId = null;
        $this->detalle = [];
        $this->traspasoRechazoId = null;
        $this->motivoRechazo = '';
    }

    public function toggleLeidaDetalle(): void
    {
        if (! $this->detalleId) {
            return;
        }
        $leida = (bool) ($this->detalle['leida'] ?? false);
        if ($leida) {
            $this->marcarNoLeida($this->detalleId);
        } else {
            $this->marcarLeida($this->detalleId);
        }
        $this->cargarDetalle($this->detalleId);
    }

    public function irAlApartado(string $id): void
    {
        // Marca como leída antes de navegar (el frontend hará la redirección)
        $this->marcarLeida($id);
        $aviso = $this->encontrarAviso($id);
        $url = $aviso['destino_url'] ?? $aviso['url'] ?? null;
        if ($url) {
            $this->dispatch('notificacion-ir', url: $url);
        }
    }

    private function cargarDetalle(string $id): void
    {
        $aviso = $this->encontrarAviso($id);
        if (! $aviso) {
            // Intento via AlertasFeed::encontrar (solo feed) como respaldo
            $sucursalId = (int) ($this->selectedSucursalId ?? 0);
            $found = AlertasFeed::encontrar($sucursalId, $id, auth()->id());
            if ($found) {
                $this->detalle = AlertasFeed::serializar($found, (bool) ($found['leida'] ?? false));
                $this->detalle['grupo'] = $found['tipo'] ?? $found['grupo'] ?? 'general';
                $this->detalle['nivel'] = $found['nivel'] ?? null;
                $this->detalle['avatar'] = $found['avatar'] ?? '•';
                $this->detalle['fecha_texto'] = $found['fecha_texto'] ?? null;
                $this->detalle['tiempo_corto'] = AlertasFeed::tiempoCorto($found['fecha'] ?? null);

                return;
            }
            $this->detalle = [];

            return;
        }
        // Enriquecer con campos serializados
        $fecha = $aviso['fecha'] ?? null;
        $this->detalle = array_merge($aviso, [
            'tiempo' => $fecha ? $fecha->diffForHumans() : 'reciente',
            'tiempo_corto' => AlertasFeed::tiempoCorto($fecha),
            'fecha_texto' => $fecha ? $fecha->format('Y-m-d H:i') : null,
            'destino_url' => $aviso['destino_url'] ?? $aviso['url'] ?? '#',
            'destino_etiqueta' => $aviso['destino_etiqueta'] ?? 'Ir al apartado',
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function encontrarAviso(string $id): ?array
    {
        $sucursalId = (int) ($this->selectedSucursalId ?? 0);
        $usuarioId = auth()->id();
        foreach (AlertasFeed::listado($sucursalId, $usuarioId) as $aviso) {
            if ($aviso['id'] === $id) {
                return $aviso;
            }
            if (isset($aviso['alerta_id']) && (string) $aviso['alerta_id'] === $id) {
                return $aviso;
            }
        }

        return null;
    }

    public function render(): View
    {
        $sucursalId = (int) ($this->selectedSucursalId ?? 0);
        $usuarioId = auth()->id();
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        $selectedSucursal = $sucursales->firstWhere('id', $sucursalId) ?? $sucursales->first();

        $avisos = $sucursalId ? AlertasFeed::listado($sucursalId, $usuarioId) : [];
        $noLeidas = collect($avisos)->where('leida', false)->count();

        $tipos = [
            'stock' => collect($avisos)->where('grupo', 'stock')->count(),
            'caducidad' => collect($avisos)->where('grupo', 'caducidad')->count(),
            'traspasos' => collect($avisos)->where('grupo', 'traspasos')->count(),
            'ventas' => collect($avisos)->where('grupo', 'ventas')->count(),
        ];

        // Filtro principal
        $filtradas = match ($this->filtro) {
            'no_leidas' => array_values(array_filter($avisos, fn (array $a): bool => ($a['leida'] ?? false) === false)),
            'caducidad' => array_values(array_filter($avisos, fn (array $a): bool => ($a['grupo'] ?? $a['tipo']) === 'caducidad' && ($this->nivel === 'todos' || ($a['nivel'] ?? '') === $this->nivel))),
            'stock', 'traspasos', 'ventas' => array_values(array_filter($avisos, fn (array $a): bool => ($a['grupo'] ?? $a['tipo']) === $this->filtro)),
            default => $avisos,
        };

        // Búsqueda en cliente (título + detalle)
        if (trim($this->busqueda) !== '') {
            $q = mb_strtolower(trim($this->busqueda));
            $filtradas = array_values(array_filter($filtradas, function (array $a) use ($q): bool {
                return str_contains(mb_strtolower($a['titulo'] ?? ''), $q)
                    || str_contains(mb_strtolower($a['detalle'] ?? ''), $q)
                    || str_contains(mb_strtolower($a['mensaje'] ?? ''), $q);
            }));
        }

        $conteo = $sucursalId ? AlertasResumen::counts($sucursalId) : ['pendientes' => 0, 'rojos' => 0, 'total' => 0];

        return view('livewire.centro-notificaciones', [
            'sucursales' => $sucursales,
            'selectedSucursal' => $selectedSucursal,
            'notificaciones' => $filtradas,
            'totalAvisos' => count($avisos),
            'totalNoLeidas' => $noLeidas,
            'tipos' => $tipos,
            'conteo' => $conteo,
        ]);
    }
}
