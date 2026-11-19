<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\ProductoPresentacion;
use App\Models\ProductoVenta;
use App\Models\Venta;

/**
 * Consulta el desglose de productos vendidos en una venta concreta.
 *
 * Toda la consulta y los cálculos viven aquí para que la pantalla y el modal
 * solo consuman datos ya resueltos.
 */
class DetallesVentasService
{
    /**
     * Devuelve el desglose de productos de la venta indicada.
     *
     * @return array{
     *     venta: array{id: int, id_sucursal: int, folio: string, fecha: string|null, total: float},
     *     productos: array<int, array{producto: string, presentacion: string, cantidad: int, precio_unitario: float, total: float}>,
     *     subtotal: float
     * }|null
     */
    public function obtenerDetalle(int $idVenta): ?array
    {
        $venta = Venta::query()
            ->with(['detalles.productoRelacion.presentacionesPrecio.presentacion'])
            ->find($idVenta);

        if (! $venta instanceof Venta) {
            return null;
        }

        $productos = $venta->detalles
            ->map(function (ProductoVenta $detalle): array {
                $producto = $detalle->productoRelacion;
                $cantidad = (int) $detalle->cantidad;
                $precioUnitario = (float) $detalle->precio_unidad;

                return [
                    'producto' => $producto?->nombre_producto ?? 'Producto no disponible',
                    'presentacion' => $this->resolverPresentacion($producto, $precioUnitario),
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'total' => round($cantidad * $precioUnitario, 2),
                ];
            })
            ->values()
            ->all();

        $subtotal = round((float) collect($productos)->sum('total'), 2);

        return [
            'venta' => [
                'id' => (int) $venta->id,
                'id_sucursal' => (int) $venta->id_sucursal,
                'folio' => (string) $venta->folio,
                'fecha' => $venta->creado_en?->format('d/m/Y H:i'),
                'total' => (float) $venta->total,
            ],
            'productos' => $productos,
            'subtotal' => $subtotal,
        ];
    }

    /**
     * Resuelve la presentación vendida comparando el precio unitario con las
     * presentaciones registradas del producto; si no coincide, usa la
     * presentación principal.
     */
    private function resolverPresentacion(?Producto $producto, float $precioUnitario): string
    {
        if (! $producto instanceof Producto) {
            return '—';
        }

        $relacion = $producto->presentacionesPrecio->first(
            fn (ProductoPresentacion $presentacion): bool => abs((float) $presentacion->precio_presentacion - $precioUnitario) < 0.005
        );

        $nombre = $relacion?->presentacion?->presentacion
            ?? $producto->presentacionPrincipalDe()?->presentacion?->presentacion;

        return filled($nombre) ? (string) $nombre : '—';
    }
}
