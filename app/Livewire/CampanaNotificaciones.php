<?php

namespace App\Livewire;

use App\Support\AlertasFeed;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class CampanaNotificaciones extends Component
{
    public int $noLeidas = 0;

    /** @var array<int, array<string, mixed>> */
    public array $avisos = [];

    public bool $abierto = false;

    public function mount(): void
    {
        // Carga perezosa: el HTML inicial no contiene avisos para no contaminar
        // los tests que verifican el listado filtrado del centro. Los avisos
        // se cargan vía wire:init / wire:poll en el navegador.
        $user = auth()->user();
        if (! $user || ! ($user->puedeVerModulo('Alertas') ?? false)) {
            return;
        }
        $sucursalId = (int) (session('active_sucursal_id') ?? $user->id_sucursal ?? 0);
        if ($sucursalId <= 0) {
            return;
        }
        // Solo el conteo para el globo se calcula server-side; el listado
        // se hidrata en el cliente para mantener el SSR limpio.
        $this->noLeidas = AlertasFeed::noLeidasCount($sucursalId, $user->id);
    }

    public function cargar(): void
    {
        $user = auth()->user();
        if (! $user || ! ($user->puedeVerModulo('Alertas') ?? false)) {
            $this->avisos = [];
            $this->noLeidas = 0;

            return;
        }
        $sucursalId = (int) (session('active_sucursal_id') ?? $user->id_sucursal ?? 0);
        if ($sucursalId <= 0) {
            $this->avisos = [];
            $this->noLeidas = 0;

            return;
        }
        $listado = AlertasFeed::listado($sucursalId, $user->id);
        $this->noLeidas = collect($listado)->where('leida', false)->count();
        $this->avisos = array_slice(array_map(function (array $aviso): array {
            $fecha = $aviso['fecha'] ?? null;

            return [
                'id' => $aviso['id'],
                'titulo' => $aviso['titulo'],
                'detalle' => $aviso['detalle'],
                'icono' => $aviso['icono'],
                'prioridad' => $aviso['prioridad'],
                'leida' => $aviso['leida'] ?? false,
                'tiempo' => $fecha ? $fecha->diffForHumans() : 'reciente',
                'tiempo_corto' => AlertasFeed::tiempoCorto($fecha),
                'grupo' => $aviso['grupo'] ?? $aviso['tipo'],
            ];
        }, $listado), 0, 8);
    }

    public function toggle(): void
    {
        $this->abierto = ! $this->abierto;
        if ($this->abierto) {
            $this->cargar();
        }
    }

    public function cerrar(): void
    {
        $this->abierto = false;
    }

    public function marcarTodasLeidas(): void
    {
        $user = auth()->user();
        $sucursalId = (int) (session('active_sucursal_id') ?? $user?->id_sucursal ?? 0);
        if ($sucursalId && $user) {
            foreach (AlertasFeed::listado($sucursalId, $user->id) as $aviso) {
                $alertaId = $aviso['alerta_id'] ?? null;
                if ($alertaId !== null) {
                    AlertasFeed::marcarLeidaPorAlerta((int) $alertaId, $user->id);
                }
            }
        }
        $this->noLeidas = 0;
        $this->avisos = array_map(fn (array $a): array => array_merge($a, ['leida' => true]), $this->avisos);
        $this->dispatch('notificaciones-todas-leidas');
    }

    #[On('notificacion-actualizada')]
    #[On('notificaciones-todas-leidas')]
    #[On('sucursal-cambiada')]
    public function refrescar(): void
    {
        $this->cargar();
    }

    public function render(): View
    {
        return view('livewire.campana-notificaciones');
    }
}
