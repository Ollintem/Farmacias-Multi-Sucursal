<?php

use App\Livewire\PuntoDeVenta;
use App\Models\Caja;
use App\Models\Inventario;
use App\Models\Lote;
use App\Models\PresentacionProducto;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;
use Livewire\Livewire;

function contextoPosStockSucursal(object $test): array
{
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Stock POS',
        'direccion' => 'Av. Stock 100',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $rol = Rol::firstOrCreate(
        ['tipo_rol' => 'SuperAdmin'],
        ['descripcion' => 'Acceso total']
    );

    $user = User::factory()->create([
        'id_sucursal' => $sucursal->id,
        'id_rol' => $rol->id,
    ]);

    Caja::firstOrCreate(['id_sucursal' => $sucursal->id]);
    $caja = PresentacionProducto::create(['presentacion' => 'Caja', 'descripcion' => '']);

    $test->actingAs($user);
    session(['active_sucursal_id' => $sucursal->id]);

    return [$sucursal, $caja];
}

function loteEnSucursalPos(Producto $producto, Sucursal $sucursal, int $unidades, string $caducidad): Lote
{
    $lote = Lote::create([
        'folio' => uniqid('L-STS-'),
        'stock_lote' => $unidades,
        'id_producto' => $producto->id,
        'entregado_en' => now(),
        'fecha_caducidad' => $caducidad,
    ]);

    Inventario::create([
        'id_sucursal' => $sucursal->id,
        'id_lote' => $lote->id,
        'stock' => $unidades,
    ]);

    Inventario::reflejarStockGlobal($producto->id);

    return $lote;
}

test('confirmar venta consume los lotes de la sucursal activa por caducidad mas vieja primero', function () {
    [$sucursal, $caja] = contextoPosStockSucursal($this);

    $producto = Producto::create([
        'codigo_barras' => '7500000000101',
        'nombre_producto' => 'Ibuprofeno 400mg FEFO',
        'descripcion' => '',
        'stock' => 0,
        'precio' => 5.0,
        'id_presentacion' => $caja->id,
        'es_controlado' => false,
        'es_activo' => true,
    ]);

    $loteViejo = loteEnSucursalPos($producto, $sucursal, 10, '2026-12-31');
    $loteNuevo = loteEnSucursalPos($producto, $sucursal, 10, '2027-12-31');

    $component = Livewire::test(PuntoDeVenta::class);

    $component->call('agregarAlCarrito', $producto->id);
    $component->call('actualizarCantidad', 0, 15);
    $component->call('abrirCobro');
    $component->set('metodoPago', 'efectivo');
    $component->set('efectivoRecibido', 100);
    $component->call('confirmarVenta');

    $component->assertSet('errorVenta', '');
    $this->assertSame(0, (int) Inventario::where('id_lote', $loteViejo->id)->value('stock'));
    $this->assertSame(5, (int) Inventario::where('id_lote', $loteNuevo->id)->value('stock'));
    $this->assertSame(5, $producto->fresh()->stock);
});

test('la venta se bloquea si la sucursal activa no tiene stock aunque otra si tenga', function () {
    [$sucursal, $caja] = contextoPosStockSucursal($this);

    $otraSucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Con Stock',
        'direccion' => 'Av. Llena 200',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $producto = Producto::create([
        'codigo_barras' => '7500000000102',
        'nombre_producto' => 'Naproxeno 250mg Agotado',
        'descripcion' => '',
        'stock' => 0,
        'precio' => 5.0,
        'id_presentacion' => $caja->id,
        'es_controlado' => false,
        'es_activo' => true,
    ]);

    $loteAjeno = loteEnSucursalPos($producto, $otraSucursal, 10, '2027-12-31');

    $component = Livewire::test(PuntoDeVenta::class);

    $component->call('agregarAlCarrito', $producto->id);
    $component->assertSet('carrito', []);

    $component->set('carrito', [[
        'producto_id' => $producto->id,
        'nombre' => $producto->nombre_producto,
        'precio' => 5.0,
        'cantidad' => 2,
        'stock' => 10,
        'presentacion' => 'Caja',
        'es_controlado' => false,
        'tipo_venta' => 'unidad',
    ]]);
    $component->call('abrirCobro');
    $component->set('metodoPago', 'efectivo');
    $component->set('efectivoRecibido', 100);
    $component->call('confirmarVenta');

    $component->assertSet(
        'errorVenta',
        "Stock insuficiente para '{$producto->nombre_producto}' en esta sucursal. Disponible: 0, solicitado: 2 unidades."
    );
    $this->assertSame(10, (int) Inventario::where('id_lote', $loteAjeno->id)->value('stock'));
    $this->assertSame(10, $producto->fresh()->stock);
});

test('las tarjetas del pos muestran el stock de la sucursal activa', function () {
    [$sucursal, $caja] = contextoPosStockSucursal($this);

    $otraSucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Ajena',
        'direccion' => 'Av. Ajena 300',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $producto = Producto::create([
        'codigo_barras' => '7500000000103',
        'nombre_producto' => 'Paracetamol 500mg Stock',
        'descripcion' => '',
        'stock' => 0,
        'precio' => 2.5,
        'id_presentacion' => $caja->id,
        'es_controlado' => false,
        'es_activo' => true,
    ]);

    loteEnSucursalPos($producto, $sucursal, 10, '2027-12-31');
    loteEnSucursalPos($producto, $otraSucursal, 25, '2027-12-31');

    $html = Livewire::test(PuntoDeVenta::class)->html();

    expect($html)
        ->toContain('10 disp.')
        ->not->toContain('35 disp.');
});
