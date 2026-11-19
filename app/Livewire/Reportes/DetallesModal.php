<?php

namespace App\Livewire\Reportes;

use App\Models\User;
use App\Services\DetallesVentasService;
use App\Services\VentasReporteService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Modal de solo lectura con los productos vendidos en una venta del reporte.
 */
class DetallesModal extends Component
{
    public bool $abierto = false;

    /** @var array<string, mixed>|null */
    public ?array $detalle = null;

    /**
     * Carga el desglose de la venta seleccionada y abre el modal.
     */
    #[On('ver-detalle-venta')]
    public function abrir(
        int $idVenta,
        DetallesVentasService $detalles,
        VentasReporteService $ventasReporte
    ): void {
        $usuario = Auth::user();

        if (! $usuario instanceof User) {
            abort(403, 'No tienes permiso para consultar esta venta.');
        }

        $detalle = $detalles->obtenerDetalle($idVenta);

        if ($detalle === null) {
            $this->cerrar();

            return;
        }

        $puedeConsultarVarias = $ventasReporte->puedeConsultarVariasSucursales($usuario);

        if (! $puedeConsultarVarias && (int) $usuario->id_sucursal !== $detalle['venta']['id_sucursal']) {
            abort(403, 'No tienes permiso para consultar esta venta.');
        }

        $this->detalle = $detalle;
        $this->abierto = true;
    }

    public function cerrar(): void
    {
        $this->abierto = false;
        $this->detalle = null;
    }

    public function render(): View
    {
        return view('pages.reportes.components.DetallesModal');
    }
}
