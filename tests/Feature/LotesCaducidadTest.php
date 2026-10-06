<?php

use App\Models\Inventario;
use App\Models\Lote;
use App\Models\PresentacionProducto;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;

it('loads the lotes and expiry page', function () {
    $rol = Rol::firstOrCreate(['tipo_rol' => 'SuperAdmin'], ['descripcion' => 'Acceso total']);

    $user = User::factory()->create(['id_rol' => $rol->id]);

    $this->actingAs($user)
        ->get(route('lotes.index'))
        ->assertOk();
});

it('loads the lote creation form with pedido-driven proveedor and sucursal selects', function () {
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Lotes Create',
        'direccion' => 'Calle 1',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $rol = Rol::firstOrCreate(['tipo_rol' => 'SuperAdmin'], ['descripcion' => 'Acceso total']);

    $user = User::factory()->create([
        'id_rol' => $rol->id,
        'id_sucursal' => $sucursal->id,
    ]);

    Proveedor::create([
        'nombre_proveedor' => 'Proveedor Demo',
        'direccion' => 'Calle 2',
        'telefono' => '5550000000',
        'correo' => 'demo@proveedor.com',
        'unidad_entrega' => 'caja',
    ]);

    $contenido = $this->actingAs($user)
        ->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('lotes.create'))
        ->assertOk()
        ->getContent();

    expect($contenido)
        ->toContain('name="id_pedido"')
        ->toContain('name="id_proveedor"')
        ->toContain('name="sucursal"')
        ->toContain('vienen del pedido')
        ->toContain('Proveedor Demo')
        ->toContain('Lotes Create');
});

test('la tabla de lotes muestra la cantidad inicial y el restante de la sucursal', function () {
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Lotes Restante',
        'direccion' => 'Calle 9',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $rol = Rol::firstOrCreate(['tipo_rol' => 'SuperAdmin'], ['descripcion' => 'Acceso total']);
    $user = User::factory()->create(['id_rol' => $rol->id, 'id_sucursal' => $sucursal->id]);
    $caja = PresentacionProducto::create(['presentacion' => 'Caja', 'descripcion' => '']);

    $producto = Producto::create([
        'codigo_barras' => '7500000000201',
        'nombre_producto' => 'Lote Restante Demo',
        'descripcion' => '',
        'stock' => 0,
        'precio' => 10.0,
        'id_presentacion' => $caja->id,
        'es_controlado' => false,
        'es_activo' => true,
    ]);

    $lote = Lote::create([
        'folio' => 'L-REST-001',
        'stock_lote' => 100,
        'id_producto' => $producto->id,
        'id_presentacion' => $caja->id,
        'entregado_en' => now(),
        'fecha_caducidad' => '2027-12-31',
    ]);

    Inventario::create([
        'id_sucursal' => $sucursal->id,
        'id_lote' => $lote->id,
        'stock' => 60,
    ]);

    $contenido = $this->actingAs($user)
        ->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('lotes.index'))
        ->assertOk()
        ->getContent();

    expect($contenido)
        ->toContain('Cantidad inicial')
        ->toContain('Restante')
        ->toContain('100 uds.')
        ->toContain('60 uds.');
});

test('un lote sin filas de inventario muestra el restante sin datos', function () {
    $rol = Rol::firstOrCreate(['tipo_rol' => 'SuperAdmin'], ['descripcion' => 'Acceso total']);
    $user = User::factory()->create(['id_rol' => $rol->id]);
    $caja = PresentacionProducto::create(['presentacion' => 'Caja', 'descripcion' => '']);

    $producto = Producto::create([
        'codigo_barras' => '7500000000202',
        'nombre_producto' => 'Lote Sin Inventario',
        'descripcion' => '',
        'stock' => 0,
        'precio' => 10.0,
        'id_presentacion' => $caja->id,
        'es_controlado' => false,
        'es_activo' => true,
    ]);

    Lote::create([
        'folio' => 'L-SIN-INV-001',
        'stock_lote' => 40,
        'id_producto' => $producto->id,
        'id_presentacion' => $caja->id,
        'entregado_en' => now(),
        'fecha_caducidad' => '2027-12-31',
    ]);

    $contenido = $this->actingAs($user)
        ->get(route('lotes.index'))
        ->assertOk()
        ->getContent();

    expect($contenido)->toContain('—');
});

test('las tarjetas de estado filtran la tabla y resaltan la seleccion', function () {
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Lotes Filtro',
        'direccion' => 'Calle 11',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $rol = Rol::firstOrCreate(['tipo_rol' => 'SuperAdmin'], ['descripcion' => 'Acceso total']);
    $user = User::factory()->create(['id_rol' => $rol->id, 'id_sucursal' => $sucursal->id]);
    $caja = PresentacionProducto::create(['presentacion' => 'Caja', 'descripcion' => '']);

    $producto = Producto::create([
        'codigo_barras' => '7500000000301',
        'nombre_producto' => 'Producto Filtro Tarjetas',
        'descripcion' => '',
        'stock' => 0,
        'precio' => 10.0,
        'id_presentacion' => $caja->id,
        'es_controlado' => false,
        'es_activo' => true,
    ]);

    $folios = [
        'vigente' => 'L-FILT-VIG',
        'por-caducar' => 'L-FILT-PC',
        'caducados' => 'L-FILT-CAD',
    ];
    $fechas = [
        'L-FILT-VIG' => now()->addYear(),
        'L-FILT-PC' => now()->addDays(20),
        'L-FILT-CAD' => '2020-01-01',
    ];

    foreach ($folios as $folio) {
        $lote = Lote::create([
            'folio' => $folio,
            'stock_lote' => 10,
            'id_producto' => $producto->id,
            'id_presentacion' => $caja->id,
            'fecha_caducidad' => $fechas[$folio],
        ]);

        Inventario::create([
            'id_sucursal' => $sucursal->id,
            'id_lote' => $lote->id,
            'stock' => 10,
        ]);
    }

    $cliente = $this->actingAs($user)->withSession(['active_sucursal_id' => $sucursal->id]);

    // Sin filtro se ven los tres lotes.
    $cliente->get(route('lotes.index'))
        ->assertOk()
        ->assertSee('L-FILT-VIG')
        ->assertSee('L-FILT-PC')
        ->assertSee('L-FILT-CAD');

    $cliente->get(route('lotes.index', ['estado' => 'vigentes']))
        ->assertOk()
        ->assertSee('L-FILT-VIG')
        ->assertDontSee('L-FILT-PC')
        ->assertDontSee('L-FILT-CAD');

    $cliente->get(route('lotes.index', ['estado' => 'por-caducar']))
        ->assertOk()
        ->assertSee('L-FILT-PC')
        ->assertDontSee('L-FILT-VIG')
        ->assertDontSee('L-FILT-CAD');

    $cliente->get(route('lotes.index', ['estado' => 'caducados']))
        ->assertOk()
        ->assertSee('L-FILT-CAD')
        ->assertDontSee('L-FILT-VIG')
        ->assertDontSee('L-FILT-PC');

    // Un estado desconocido vuelve al conjunto completo.
    $cliente->get(route('lotes.index', ['estado' => 'inventado']))
        ->assertOk()
        ->assertSee('L-FILT-VIG')
        ->assertSee('L-FILT-PC')
        ->assertSee('L-FILT-CAD');

    // La tarjeta activa queda resaltada y las demás siguen como enlaces.
    $contenido = $cliente->get(route('lotes.index', ['estado' => 'caducados']))
        ->assertOk()
        ->getContent();

    expect($contenido)
        ->toContain('module-stat-filtro-activo')
        ->toContain('estado=vigentes');
});
