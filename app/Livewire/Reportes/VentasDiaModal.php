<?php

namespace App\Livewire\Reportes;

use App\Models\User;
use App\Models\Venta;
use App\Services\VentasReporteService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Modal de solo lectura con las ventas registradas en un día del reporte.
 *
 * Recibe el día desde las tarjetas de la pantalla de reportes (semana/mes),
 * valida que el usuario pueda consultar la sucursal y reutiliza el servicio
 * de ventas para obtener exactamente los mismos totales del reporte.
 */
class VentasDiaModal extends Component
{
    public bool $abierto = false;

    public string $etiqueta = '';

    public ?string $sucursalNombre = null;

    public bool $consolidado = false;

    /** @var array<int, array<string, mixed>> */
    public array $ventas = [];

    /** @var array<string, mixed> */
    public array $totales = [];

    #[On('ver-ventas-dia')]
    public function abrir(string $fecha, VentasReporteService $servicio, ?int $sucursal = null): void
    {
        $usuario = Auth::user();

        if (! $usuario instanceof User) {
            abort(403, 'No tienes permiso para consultar estas ventas.');
        }

        $this->autorizarSucursal($usuario, $sucursal, $servicio);

        $reporte = $servicio->ventasPorDia($fecha, $sucursal);

        $this->etiqueta = $reporte['etiqueta'];
        $this->sucursalNombre = $reporte['sucursal']?->nombre_sucursal;
        $this->consolidado = $sucursal === null;
        $this->totales = $reporte['totales'];
        $this->ventas = $reporte['ventas']
            ->map(fn (Venta $venta): array => [
                'id' => (int) $venta->id,
                'folio' => (string) $venta->folio,
                'hora' => $venta->creado_en?->format('H:i') ?? '—',
                'sucursal' => $venta->sucursal?->nombre_sucursal ?? '—',
                'caja' => $venta->corteCaja?->caja ?? '—',
                'turno' => $venta->corteCaja?->turno ?? '—',
                'metodo' => $venta->pago?->metodo ?? 'Sin pago',
                'unidades' => (int) $venta->detalles->sum('cantidad'),
                'descuento' => (float) $venta->descuento,
                'total' => (float) $venta->total,
            ])
            ->values()
            ->all();
        $this->abierto = true;
    }

    public function cerrar(): void
    {
        $this->abierto = false;
        $this->ventas = [];
        $this->totales = [];
    }

    /**
     * Impide consultar una sucursal fuera de las permitidas para el usuario.
     */
    private function autorizarSucursal(User $usuario, ?int $sucursal, VentasReporteService $servicio): void
    {
        if (! $servicio->puedeConsultarVariasSucursales($usuario)) {
            if ((int) $usuario->id_sucursal !== $sucursal) {
                abort(403, 'No tienes permiso para consultar esta sucursal.');
            }

            return;
        }

        if ($sucursal !== null && ! $servicio->sucursalesDisponibles($usuario)->contains('id', $sucursal)) {
            abort(403, 'No tienes permiso para consultar esta sucursal.');
        }
    }

    public function render(): View
    {
        return view('livewire.reportes.ventas-dia-modal');
    }
}
