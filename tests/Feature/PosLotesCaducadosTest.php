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

function contextoPosCaducados(object $test): array
{
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Caducados POS',
        'direccion' => 'Av. Caducados 1',
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

/**
 * Crea un producto vendible por unidad en el POS.
 */
function productoCaducadoPos(PresentacionProducto $caja, string $codigo, string $nombre): Producto
{
    $producto = Producto::create([
        'codigo_barras' => $codigo,
        'nombre_producto' => $nombre,
        'descripcion' => '',
        'es_controlado' => false,
        'es_activo' => true,
    ]);

    registrarPrecioUnidad($producto, 5.0);

    return $producto;
}

/**
 * Crea un lote con inventario en la sucursal; `caducidad` null deja el lote
 * sin fecha (no se considera caducado).
 */
function loteCaducadoPos(Producto $producto, Sucursal $sucursal, int $unidades, ?string $caducidad): Lote
{
    $lote = Lote::create([
        'folio' => uniqid('L-CAD-'),
        'stock_lote' => $unidades,
        'id_producto' => $producto->id,
        'entregado_en' => now(),
        'fecha_caducidad' => $caducidad,
    ]);

    Inventario::create([
        'id_sucursal' => $sucursal->id,
        'id_lote' => $lote->id,
        'stock_individual' => $unidades,
    ]);

    return $lote;
}

test('la venta ignora los lotes caducados y consume los vigentes por FEFO', function () {
    [$sucursal, $caja] = contextoPosCaducados($this);

    $producto = productoCaducadoPos($caja, '7500000000401', 'Ibuprofeno Caducado');

    $loteCaducado = loteCaducadoPos($producto, $sucursal, 40, now()->subYear()->toDateString());
    $loteVigenteCercano = loteCaducadoPos($producto, $sucursal, 10, now()->addMonths(6)->toDateString());
    $loteVigenteLejano = loteCaducadoPos($producto, $sucursal, 10, now()->addYears(2)->toDateString());

    $component = Livewire::test(PuntoDeVenta::class);

    // El disponible para el carrito excluye las 40 uds. caducadas.
    $component->call('agregarAlCarrito', $producto->id);
    $component->call('actualizarCantidad', 0, 12);
    $component->call('abrirCobro');
    $component->set('metodoPago', 'efectivo');
    $component->set('efectivoRecibido', 100);
    $component->call('confirmarVenta');

    $component->assertSet('errorVenta', '');
    $component->assertDispatched('venta-completada');
    $component->assertDispatched('stock-caducado-ignorado', unidades: 40);

    // FEFO sigue intacto entre los vigentes: primero el más cercano a caducar.
    $this->assertSame(40, (int) Inventario::where('id_lote', $loteCaducado->id)->value('stock_individual'));
    $this->assertSame(0, (int) Inventario::where('id_lote', $loteVigenteCercano->id)->value('stock_individual'));
    $this->assertSame(8, (int) Inventario::where('id_lote', $loteVigenteLejano->id)->value('stock_individual'));
});

test('un producto cuyo stock sea solo caducado no entra al carrito', function () {
    [$sucursal, $caja] = contextoPosCaducados($this);

    $producto = productoCaducadoPos($caja, '7500000000418', 'Amoxicilina Solo Caducada');
    loteCaducadoPos($producto, $sucursal, 20, now()->subDays(10)->toDateString());

    $component = Livewire::test(PuntoDeVenta::class);

    $component->call('agregarAlCarrito', $producto->id);
    $component->assertSet('carrito', []);

    $component->set('carrito', [[
        'producto_id' => $producto->id,
        'nombre' => $producto->nombre_producto,
        'precio' => 5.0,
        'cantidad' => 1,
        'stock' => 20,
        'presentacion' => '',
        'es_controlado' => false,
        'tipo_venta' => 'unidad',
    ]]);
    $component->call('abrirCobro');
    $component->set('metodoPago', 'efectivo');
    $component->set('efectivoRecibido', 100);
    $component->call('confirmarVenta');

    $component->assertSet(
        'errorVenta',
        "Stock insuficiente para '{$producto->nombre_producto}' en esta sucursal. Disponible: 0, solicitado: 1 unidades."
    );

    // El inventario caducado queda intacto.
    $this->assertSame(20, (int) Inventario::where('id_lote', Lote::where('id_producto', $producto->id)->firstOrFail()->id)->value('stock_individual'));
});

test('un lote sin fecha de caducidad sigue vendible y no lanza el aviso', function () {
    [$sucursal, $caja] = contextoPosCaducados($this);

    $producto = productoCaducadoPos($caja, '7500000000425', 'Paracetamol Sin Fecha');
    loteCaducadoPos($producto, $sucursal, 10, null);

    $component = Livewire::test(PuntoDeVenta::class);

    $component->call('agregarAlCarrito', $producto->id);
    $component->call('abrirCobro');
    $component->set('metodoPago', 'efectivo');
    $component->set('efectivoRecibido', 100);
    $component->call('confirmarVenta');

    $component->assertSet('errorVenta', '');
    $component->assertDispatched('venta-completada');
    $component->assertNotDispatched('stock-caducado-ignorado');

    $lote = Lote::where('id_producto', $producto->id)->firstOrFail();

    $this->assertSame(9, (int) Inventario::where('id_lote', $lote->id)->value('stock_individual'));
});

test('el catálogo muestra stock vendible, badge caducado y botón inactivo para producto solo caducado', function () {
    [$sucursal, $caja] = contextoPosCaducados($this);

    $producto = productoCaducadoPos($caja, '7500000000432', 'Naproxeno Solo Caducado');
    loteCaducadoPos($producto, $sucursal, 20, now()->subDays(5)->toDateString());

    $component = Livewire::test(PuntoDeVenta::class);

    // El stock del catálogo excluye lotes caducados: nada de "20 disp.".
    $component->assertSee('0 disp.');
    $component->assertSee('Caducado');
    $component->assertDontSee('¡Últimas!');
    $component->assertSee('Sin existencias vigentes — solo lotes caducados');
});

test('agregar un producto solo caducado despacha pos-error y no llena el carrito', function () {
    [$sucursal, $caja] = contextoPosCaducados($this);

    $producto = productoCaducadoPos($caja, '7500000000439', 'Cefalexina Solo Caducada');
    loteCaducadoPos($producto, $sucursal, 15, now()->subDays(3)->toDateString());

    $component = Livewire::test(PuntoDeVenta::class);

    $component->call('agregarAlCarrito', $producto->id);

    $component->assertSet('carrito', []);
    $component->assertDispatched('pos-error');
});

test('al alcanzar la cantidad máxima disponible el POS despacha pos-error', function () {
    [$sucursal, $caja] = contextoPosCaducados($this);

    $producto = productoCaducadoPos($caja, '7500000000446', 'Aspirina Vigente');
    loteCaducadoPos($producto, $sucursal, 1, now()->addMonths(4)->toDateString());

    $component = Livewire::test(PuntoDeVenta::class);

    $component->call('agregarAlCarrito', $producto->id);
    $component->assertSet('carrito.0.cantidad', 1);

    // Segundo intento: ya no hay más disponible, debe avisar y no subir.
    $component->call('agregarAlCarrito', $producto->id);

    $component->assertSet('carrito.0.cantidad', 1);
    $component->assertDispatched('pos-error');
});
