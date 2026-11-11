<?php

use App\Livewire\PuntoDeVenta;
use App\Models\CorteCaja;
use App\Models\Inventario;
use App\Models\Lote;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Venta;
use Livewire\Livewire;

/**
 * Contexto mínimo del POS: sucursal activa, cajero SuperAdmin y un producto
 * con stock vigente en un lote de la sucursal.
 *
 * @return array{0: Sucursal, 1: Producto, 2: User}
 */
function contextoPosVentasConsecutivas(bool $conCajaAbierta = true): array
{
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Venta Consecutiva',
        'direccion' => 'Av. Consecutiva 100',
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

    $producto = Producto::create([
        'codigo_barras' => '7500000000901',
        'nombre_producto' => 'Paracetamol 500mg Consecutivo',
        'descripcion' => '',
        'es_controlado' => false,
        'es_activo' => true,
    ]);

    registrarPrecioUnidad($producto, 10.0);

    $lote = Lote::create([
        'folio' => 'L-CONSEC-01',
        'stock_lote' => 20,
        'id_producto' => $producto->id,
        'entregado_en' => now(),
        'fecha_caducidad' => '2028-12-31',
    ]);

    Inventario::create([
        'id_sucursal' => $sucursal->id,
        'id_lote' => $lote->id,
        'stock_individual' => 20,
    ]);

    if ($conCajaAbierta) {
        CorteCaja::create([
            'id_sucursal' => $sucursal->id,
            'id_usuario' => $user->id,
            'caja' => 'Caja 1',
            'turno' => 'Matutino',
            'efectivo_inicial' => 0,
            'efectivo_declarado' => 0,
            'efectivo_esperado' => 0,
            'diferencia' => 0,
            'fecha_inicio' => now(),
            'estado' => 'abierto',
        ]);
    }

    test()->actingAs($user);
    session(['active_sucursal_id' => $sucursal->id]);

    return [$sucursal, $producto, $user];
}

test('permite cobrar una segunda venta despues de completar la primera', function () {
    [, $producto] = contextoPosVentasConsecutivas();

    $component = Livewire::test(PuntoDeVenta::class);

    $component->call('agregarAlCarrito', $producto->id);
    $component->call('abrirCobro');
    $component->set('metodoPago', 'efectivo');
    $component->set('efectivoRecibido', 20);
    $component->call('confirmarVenta');
    $component->call('imprimirTicket');

    $component->assertSet('errorVenta', '');
    $component->assertSet('carrito', []);
    $component->assertSet('procesando', false);
    $component->assertSet('abriendoCobro', false);
    expect(Venta::count())->toBe(1);

    // Tras cobrar, el POS debe seguir operando: agregar y cobrar de nuevo.
    $component->call('agregarAlCarrito', $producto->id);
    $component->assertSet('carrito.0.cantidad', 1);

    $component->call('abrirCobro');
    $component->assertSet('abriendoCobro', true);
    $component->set('metodoPago', 'efectivo');
    $component->set('efectivoRecibido', 20);
    $component->call('confirmarVenta');
    $component->call('imprimirTicket');

    $component->assertSet('errorVenta', '');
    $component->assertSet('procesando', false);
    expect(Venta::count())->toBe(2);
});

test('permite cobrar con tarjeta varias ventas seguidas', function () {
    [, $producto] = contextoPosVentasConsecutivas();

    $component = Livewire::test(PuntoDeVenta::class);

    foreach ([1, 2] as $intento) {
        $component->call('agregarAlCarrito', $producto->id);
        $component->call('abrirCobro');
        $component->set('metodoPago', 'tarjeta');
        $component->call('confirmarVenta');
    $component->call('imprimirTicket');

        $component->assertSet('errorVenta', '');
        expect(Venta::count())->toBe($intento);
    }
});

test('sin caja abierta el POS pide activarla antes de cobrar', function () {
    [, $producto] = contextoPosVentasConsecutivas(conCajaAbierta: false);

    $component = Livewire::test(PuntoDeVenta::class);
    $component->call('agregarAlCarrito', $producto->id);

    $component->assertSee('Sin caja abierta');

    $component->call('abrirCobro');

    $component->assertSet('abriendoCobro', false)
        ->assertSet('abriendoCaja', true);

    expect(Venta::count())->toBe(0);
});

test('abrir la caja desde el POS regresa al punto de venta y permite cobrar', function () {
    [$sucursal, $producto, $user] = contextoPosVentasConsecutivas(conCajaAbierta: false);

    $this->actingAs($user)
        ->post(route('caja.abrir'), [
            'id_sucursal' => $sucursal->id,
            'caja' => 'Caja 1',
            'turno' => 'Matutino',
            'efectivo_inicial' => 100,
            'redirect_to' => 'punto-venta',
        ])
        ->assertRedirect(route('punto-venta.index', ['cobrar' => 1]))
        ->assertSessionHas('success');

    $corte = CorteCaja::where('id_sucursal', $sucursal->id)->firstOrFail();

    expect($corte->estado)->toBe('abierto')
        ->and((int) $corte->id_usuario)->toBe($user->id)
        ->and($corte->turno)->toBe('Matutino')
        ->and((float) $corte->efectivo_inicial)->toBe(100.0);

    $component = Livewire::test(PuntoDeVenta::class);
    $component->assertSee('Caja abierta: Caja 1');

    $component->call('agregarAlCarrito', $producto->id);
    $component->call('abrirCobro');
    $component->assertSet('abriendoCobro', true);

    $component->set('metodoPago', 'efectivo');
    $component->set('efectivoRecibido', 20);
    $component->call('confirmarVenta');
    $component->call('imprimirTicket');

    $component->assertSet('errorVenta', '');
    expect(Venta::count())->toBe(1)
        ->and((int) Venta::first()->id_corte_caja)->toBe($corte->id);
});

test('al regresar del alta de caja el POS retoma el cobro pendiente', function () {
    [$sucursal, $producto, $user] = contextoPosVentasConsecutivas(conCajaAbierta: false);

    // El carrito se guarda en sesión al agregar el producto.
    Livewire::test(PuntoDeVenta::class)->call('agregarAlCarrito', $producto->id);

    CorteCaja::create([
        'id_sucursal' => $sucursal->id,
        'id_usuario' => $user->id,
        'caja' => 'Caja 1',
        'turno' => 'Matutino',
        'efectivo_inicial' => 0,
        'efectivo_declarado' => 0,
        'efectivo_esperado' => 0,
        'diferencia' => 0,
        'fecha_inicio' => now(),
        'estado' => 'abierto',
    ]);

    $this->actingAs($user)
        ->get(route('punto-venta.index', ['cobrar' => 1]))
        ->assertOk()
        ->assertSee('Total a pagar');
});

test('el punto de venta muestra la caja del turno y permite activarla', function () {
    [$sucursal, , $user] = contextoPosVentasConsecutivas(conCajaAbierta: false);

    $this->actingAs($user)
        ->get(route('punto-venta.index'))
        ->assertOk()
        ->assertSee('Sin caja abierta')
        ->assertSee('Activar caja');

    CorteCaja::create([
        'id_sucursal' => $sucursal->id,
        'id_usuario' => $user->id,
        'caja' => 'Caja 1',
        'turno' => 'Matutino',
        'efectivo_inicial' => 0,
        'efectivo_declarado' => 0,
        'efectivo_esperado' => 0,
        'diferencia' => 0,
        'fecha_inicio' => now(),
        'estado' => 'abierto',
    ]);

    $this->actingAs($user)
        ->get(route('punto-venta.index'))
        ->assertOk()
        ->assertSee('Caja abierta: Caja 1')
        ->assertSee('Mañana');
});

test('la vista de caja muestra la caja activa del usuario y su turno', function () {
    [$sucursal, , $user] = contextoPosVentasConsecutivas();

    $this->actingAs($user)
        ->get(route('caja.index', ['sucursal' => $sucursal->id]))
        ->assertOk()
        ->assertSee('Tu caja está activa')
        ->assertSee('Caja 1')
        ->assertSee('Mañana');
});
