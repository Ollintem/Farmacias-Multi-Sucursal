<?php

namespace App\Livewire;

use App\Models\CorteCaja;
use App\Support\CierreCajaTurno;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Aviso del turno del cajero: recuerda cerrar la caja antes de que termine
 * y muestra la tolerancia restante. Se actualiza solo cada minuto.
 */
class AvisoTurnoCaja extends Component
{
    /**
     * Cierra las cajas vencidas y refresca el aviso (wire:poll).
     */
    public function verificar(): void
    {
        $sucursalId = $this->sucursalId();

        if ($sucursalId <= 0) {
            return;
        }

        $cerradas = CierreCajaTurno::procesar($sucursalId);

        if ($cerradas > 0) {
            $this->dispatch(
                'pos-error',
                mensaje: 'Tu caja se cerró automáticamente al vencer la tolerancia del turno. Activa una nueva caja para seguir cobrando.'
            );
        }
    }

    /**
     * Pide al POS que abra el modal de cierre de caja.
     */
    public function pedirCierre(): void
    {
        $this->dispatch('pos-cerrar-caja');
    }

    public function render(): View
    {
        $sucursalId = $this->sucursalId();
        $usuarioId = (int) (auth()->id() ?? 0);

        $corte = ($sucursalId > 0 && $usuarioId > 0)
            ? CorteCaja::query()
                ->where('id_sucursal', $sucursalId)
                ->where('id_usuario', $usuarioId)
                ->where('estado', 'abierto')
                ->orderByDesc('fecha_inicio')
                ->first()
            : null;

        $minutos = $corte?->minutosParaFinDeTurno();
        $aviso = null;

        if ($corte !== null && $minutos !== null && $minutos <= CorteCaja::AVISO_FIN_TURNO_MINUTOS) {
            $fin = $corte->finDeTurno();
            $horario = CorteCaja::HORARIOS[$corte->claveTurno()] ?? [];
            $turno = $horario['etiqueta'] ?? ucfirst($corte->claveTurno());

            if ($minutos > 0) {
                $aviso = [
                    'tono' => 'amber',
                    'titulo' => "Tu turno termina en {$minutos} min",
                    'detalle' => "{$corte->caja} · turno {$turno} · cierra y arquea antes de las ".($fin?->format('H:i') ?? '—').'.',
                ];
            } else {
                $restantes = max(0, CorteCaja::TOLERANCIA_CIERRE_MINUTOS + $minutos);
                $aviso = [
                    'tono' => 'red',
                    'titulo' => 'Tu turno terminó: cierra tu caja',
                    'detalle' => "Tolerancia restante: {$restantes} min. Si no la cierras, el sistema la cerrará automáticamente.",
                ];
            }
        }

        return view('livewire.aviso-turno-caja', [
            'aviso' => $aviso,
            'corte' => $corte,
        ]);
    }

    private function sucursalId(): int
    {
        return (int) (session('active_sucursal_id') ?? auth()->user()?->id_sucursal ?? 0);
    }
}
