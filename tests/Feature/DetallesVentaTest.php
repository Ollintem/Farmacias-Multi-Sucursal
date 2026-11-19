<?php

use App\Livewire\Reportes\DetallesModal;
use App\Models\Pago;
use App\Models\PresentacionProducto;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Venta;
use App\Services\DetallesVentasService;
use Livewire\Livewire;

/**
 * Crea una sucursal, su usuario SuperAdmin y una venta con dos productos.
 *
 * @return array{venta: Venta, usuario: User}
 */
function contextoDetallesVenta(): array
{
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Detalles Centro',
        'direccion' => 'Av. Detalles 1',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $rol = Rol::firstOrCreate(['tipo_rol' => 'SuperAdmin'], ['descripcion' => 'Acceso total']);
    $usuario = User::factory()->create([
        'id_rol' => $rol->id,
        'id_sucursal' => $sucursal->id,
    ]);

    $unidad = PresentacionProducto::create(['presentacion' => 'Unidad', 'descripcion' => '']);
    $caja = PresentacionProducto::create(['presentacion' => 'Caja', 'descripcion' => '']);

    $productoUnidad = Producto::create([
        'codigo_barras' => '7500000000101',
        'nombre_producto' => 'Paracetamol 500mg',
        'descripcion' => '',
        'es_controlado' => false,
        'es_activo' => true,
    ]);
    $productoUnidad->presentacionesPrecio()->create([
        'id_presentacion' => $unidad->id,
        'precio_presentacion' => 10,
        'unidades' => 1,
        'es_principal' => true,
    ]);

    $productoCaja = Producto::create([
        'codigo_barras' => '7500000000102',
        'nombre_producto' => 'Ibuprofeno 400mg',
        'descripcion' => '',
        'es_controlado' => false,
        'es_activo' => true,
    ]);
    $productoCaja->presentacionesPrecio()->create([
        'id_presentacion' => $caja->id,
        'precio_presentacion' => 85,
        'unidades' => 10,
        'es_principal' => true,
    ]);

    $pago = Pago::create([
        'monto' => 105,
        'metodo' => 'Efectivo',
        'estado' => 'Completado',
        'referencia' => 'REF-DETALLES',
    ]);

    $venta = Venta::create([
        'id_sucursal' => $sucursal->id,
        'folio' => 'V-DETALLES-1',
        'descuento' => 0,
        'total' => 105,
        'id_pago' => $pago->id,
        'estado' => 'Completada',
    ]);

    $venta->productos()->attach($productoUnidad->id, ['cantidad' => 2, 'precio_unidad' => 10]);
    $venta->productos()->attach($productoCaja->id, ['cantidad' => 1, 'precio_unidad' => 85]);

    return ['venta' => $venta, 'usuario' => $usuario];
}

test('el servicio de detalles calcula presentación, total por producto y subtotal', function () {
    ['venta' => $venta] = contextoDetallesVenta();

    $detalle = app(DetallesVentasService::class)->obtenerDetalle($venta->id);

    expect($detalle)->not->toBeNull()
        ->and($detalle['subtotal'])->toBe(105.0)
        ->and($detalle['productos'])->toHaveCount(2)
        ->and($detalle['productos'][0])->toMatchArray([
            'producto' => 'Paracetamol 500mg',
            'presentacion' => 'Unidad',
            'cantidad' => 2,
            'precio_unitario' => 10.0,
            'total' => 20.0,
        ])
        ->and($detalle['productos'][1])->toMatchArray([
            'producto' => 'Ibuprofeno 400mg',
            'presentacion' => 'Caja',
            'cantidad' => 1,
            'precio_unitario' => 85.0,
            'total' => 85.0,
        ]);
});

test('el servicio devuelve null cuando la venta no existe', function () {
    expect(app(DetallesVentasService::class)->obtenerDetalle(999999))->toBeNull();
});

test('el modal carga los productos de la venta seleccionada y se puede cerrar', function () {
    ['venta' => $venta, 'usuario' => $usuario] = contextoDetallesVenta();

    $this->actingAs($usuario);

    Livewire::test(DetallesModal::class)
        ->dispatch('ver-detalle-venta', idVenta: $venta->id)
        ->assertSet('abierto', true)
        ->assertSee('Paracetamol 500mg')
        ->assertSee('Ibuprofeno 400mg')
        ->assertSee('$105.00')
        ->call('cerrar')
        ->assertSet('abierto', false)
        ->assertSet('detalle', null);
});
