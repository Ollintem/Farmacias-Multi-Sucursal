<?php

namespace App\Livewire;

use App\Support\AlertasFeed;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class TopbarResumen extends Component
{
    public int $total = 0;

    public int $noLeidas = 0;

    public int $traspasos = 0;

    public int $caducidad = 0;

    public bool $esAlertas = false;

    public function mount(): void
    {
        $this->esAlertas = request()->routeIs('alertas.*');
        $this->cargar();
    }

    #[On('notificacion-actualizada')]
    #[On('notificaciones-todas-leidas')]
    #[On('sucursal-cambiada')]
    public function cargar(): void
    {
        $this->esAlertas = request()->routeIs('alertas.*');
        if (! $this->esAlertas) {
            $this->total = 0;
            $this->noLeidas = 0;
            $this->traspasos = 0;
            $this->caducidad = 0;

            return;
        }

        $user = auth()->user();
        if (! $user || ! ($user->puedeVerModulo('Alertas') ?? false)) {
            return;
        }

        $sucursalId = (int) (session('active_sucursal_id', $user->id_sucursal ?? 0) ?: 0);
        if ($sucursalId <= 0) {
            return;
        }

        $avisos = AlertasFeed::listado($sucursalId, $user->id);
        $this->total = count($avisos);
        $this->noLeidas = collect($avisos)->where('leida', false)->count();
        $this->traspasos = collect($avisos)->where('grupo', 'traspasos')->count();
        $this->caducidad = collect($avisos)->where('grupo', 'caducidad')->count();
    }

    public function render(): View
    {
        return view('livewire.topbar-resumen');
    }
}
