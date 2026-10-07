<?php

use App\Models\Categoria;
use App\Models\Inventario;
use App\Models\Lote;
use App\Models\Modulo;
use App\Models\PermisoActivado;
use App\Models\PresentacionProducto;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\Traspaso;
use App\Models\User;
use Carbon\Carbon;

/**
 * Contexto: origen con 100 uds del lote en inventario (lote con
 * stock_lote 100) y destino con 20 uds del mismo lote.
 *
 * @return array{origen: Sucursal, destino: Sucursal, usuario: User, lote: Lote, loteCorto: Lote}
 */
function crearContextoTransicion(): array
{
    $origen = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Origen Transicion',
        'direccion' => 'Av. Origen 1',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $destino = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Destino Transicion',
        'direccion' => 'Av. Destino 1',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $rol = Rol::firstOrCreate(['tipo_rol' => 'SuperAdmin'], ['descripcion' => 'Acceso total']);
    $usuario = User::factory()->create(['id_rol' => $rol->id, 'id_sucursal' => $origen->id]);

    $categoria = Categoria::create(['nombre' => 'Analgesicos Transicion']);
    $presentacion = PresentacionProducto::create(['presentacion' => 'Caja', 'descripcion' => '']);

    $producto = Producto::create([
        'codigo_barras' => '7501112223400',
        'nombre_producto' => 'Ibuprofeno Transicion',
        'descripcion' => '',
        'precio' => null,
        'id_presentacion' => $presentacion->id,
        'id_categoria' => $categoria->id,
        'es_controlado' => false,
        'es_activo' => true,
    ]);

    $lote = Lote::create([
        'folio' => 'LOT-TRA-100',
        'stock_lote' => 100,
        'id_producto' => $producto->id,
        'id_presentacion' => $presentacion->id,
        'entregado_en' => Carbon::now()->format('Y-m-d'),
        'fecha_caducidad' => Carbon::now()->addDays(60)->format('Y-m-d'),
    ]);

    Inventario::create(['id_sucursal' => $origen->id, 'id_lote' => $lote->id, 'stock' => 100]);
    Inventario::create(['id_sucursal' => $destino->id, 'id_lote' => $lote->id, 'stock' => 20]);

    $loteCorto = Lote::create([
        'folio' => 'LOT-TRA-020',
        'stock_lote' => 20,
        'id_producto' => $producto->id,
        'id_presentacion' => $presentacion->id,
        'entregado_en' => Carbon::now()->format('Y-m-d'),
        'fecha_caducidad' => Carbon::now()->addDays(60)->format('Y-m-d'),
    ]);

    Inventario::create(['id_sucursal' => $origen->id, 'id_lote' => $loteCorto->id, 'stock' => 20]);

    return compact('origen', 'destino', 'usuario', 'lote', 'loteCorto');
}

function crearTraspasoTransicion(array $ctx, int $cantidad, ?int $loteId = null, string $estado = 'pendiente'): Traspaso
{
    $traspaso = Traspaso::create([
        'sucursal_a' => $ctx['origen']->id,
        'sucursal_b' => $ctx['destino']->id,
        'pedido_por' => $ctx['usuario']->id,
        'estado' => $estado,
    ]);

    $traspaso->detalles()->create([
        'id_lote' => $loteId ?? $ctx['lote']->id,
        'cantidad' => $cantidad,
    ]);

    return $traspaso;
}

function stockTransicion(int $sucursalId, int $loteId): int
{
    return (int) Inventario::query()->where('id_sucursal', $sucursalId)->where('id_lote', $loteId)->sum('stock');
}

test('enviar descuenta el origen en inventario y lote', function () {
    $ctx = crearContextoTransicion();

    $traspaso = crearTraspasoTransicion($ctx, 30);

    $this->actingAs($ctx['usuario'])
        ->withSession(['active_sucursal_id' => $ctx['origen']->id])
        ->post(route('traspasos.enviar', $traspaso))
        ->assertRedirect(route('entradas-de-almacen.index', ['sucursal' => $ctx['origen']->id, 'tipo' => 'traspasos']));

    expect($traspaso->fresh()->estado)->toBe('enviado')
        ->and(stockTransicion($ctx['origen']->id, $ctx['lote']->id))->toBe(70)
        ->and($ctx['lote']->fresh()->stock_lote)->toBe(70);
});

test('el destino no aumenta al enviar el traspaso', function () {
    $ctx = crearContextoTransicion();

    $traspaso = crearTraspasoTransicion($ctx, 30);

    $this->actingAs($ctx['usuario'])
        ->withSession(['active_sucursal_id' => $ctx['origen']->id])
        ->post(route('traspasos.enviar', $traspaso))
        ->assertRedirect();

    expect(stockTransicion($ctx['destino']->id, $ctx['lote']->id))->toBe(20);
});

test('aceptar incrementa el destino sin volver a descontar el origen', function () {
    $ctx = crearContextoTransicion();

    $traspaso = crearTraspasoTransicion($ctx, 30);

    $this->actingAs($ctx['usuario'])
        ->withSession(['active_sucursal_id' => $ctx['origen']->id])
        ->post(route('traspasos.enviar', $traspaso))
        ->assertRedirect();

    $this->actingAs($ctx['usuario'])
        ->withSession(['active_sucursal_id' => $ctx['destino']->id])
        ->post(route('traspasos.aceptar', $traspaso))
        ->assertRedirect();

    expect($traspaso->fresh()->estado)->toBe('aceptado')
        ->and(stockTransicion($ctx['destino']->id, $ctx['lote']->id))->toBe(50)
        ->and(stockTransicion($ctx['origen']->id, $ctx['lote']->id))->toBe(70)
        ->and($ctx['lote']->fresh()->stock_lote)->toBe(70);
});

test('un traspaso no puede descontarse dos veces', function () {
    $ctx = crearContextoTransicion();

    $traspaso = crearTraspasoTransicion($ctx, 30);

    $llamada = fn () => $this->actingAs($ctx['usuario'])
        ->withSession(['active_sucursal_id' => $ctx['origen']->id])
        ->post(route('traspasos.enviar', $traspaso));

    $llamada()->assertRedirect();

    $respuesta = $llamada();
    $respuesta->assertRedirect();
    $respuesta->assertSessionHas('error');

    expect($traspaso->fresh()->estado)->toBe('enviado')
        ->and(stockTransicion($ctx['origen']->id, $ctx['lote']->id))->toBe(70)
        ->and($ctx['lote']->fresh()->stock_lote)->toBe(70);
});

test('un traspaso no puede recibirse dos veces', function () {
    $ctx = crearContextoTransicion();

    $traspaso = crearTraspasoTransicion($ctx, 30);

    $this->actingAs($ctx['usuario'])
        ->withSession(['active_sucursal_id' => $ctx['origen']->id])
        ->post(route('traspasos.enviar', $traspaso))
        ->assertRedirect();

    $aceptar = fn () => $this->actingAs($ctx['usuario'])
        ->withSession(['active_sucursal_id' => $ctx['destino']->id])
        ->post(route('traspasos.aceptar', $traspaso));

    $aceptar()->assertRedirect();

    $respuesta = $aceptar();
    $respuesta->assertRedirect();
    $respuesta->assertSessionHas('error');

    expect($traspaso->fresh()->estado)->toBe('aceptado')
        ->and(stockTransicion($ctx['destino']->id, $ctx['lote']->id))->toBe(50);
});

test('no se transfiere mas de lo disponible', function () {
    $ctx = crearContextoTransicion();

    $traspaso = crearTraspasoTransicion($ctx, 150);

    $respuesta = $this->actingAs($ctx['usuario'])
        ->withSession(['active_sucursal_id' => $ctx['origen']->id])
        ->post(route('traspasos.enviar', $traspaso));

    $respuesta->assertRedirect();
    $respuesta->assertSessionHas('error');

    expect($traspaso->fresh()->estado)->toBe('pendiente')
        ->and(stockTransicion($ctx['origen']->id, $ctx['lote']->id))->toBe(100)
        ->and($ctx['lote']->fresh()->stock_lote)->toBe(100);
});

test('si falla un detalle se revierte toda la transaccion', function () {
    $ctx = crearContextoTransicion();

    $traspaso = Traspaso::create([
        'sucursal_a' => $ctx['origen']->id,
        'sucursal_b' => $ctx['destino']->id,
        'pedido_por' => $ctx['usuario']->id,
        'estado' => 'pendiente',
    ]);
    $traspaso->detalles()->createMany([
        ['id_lote' => $ctx['lote']->id, 'cantidad' => 30],
        ['id_lote' => $ctx['loteCorto']->id, 'cantidad' => 50],
    ]);

    $respuesta = $this->actingAs($ctx['usuario'])
        ->withSession(['active_sucursal_id' => $ctx['origen']->id])
        ->post(route('traspasos.enviar', $traspaso));

    $respuesta->assertRedirect();
    $respuesta->assertSessionHas('error');

    expect($traspaso->fresh()->estado)->toBe('pendiente')
        ->and(stockTransicion($ctx['origen']->id, $ctx['lote']->id))->toBe(100)
        ->and($ctx['lote']->fresh()->stock_lote)->toBe(100)
        ->and(stockTransicion($ctx['origen']->id, $ctx['loteCorto']->id))->toBe(20)
        ->and(stockTransicion($ctx['destino']->id, $ctx['lote']->id))->toBe(20);
});

test('rechazar en transito devuelve la mercancia al origen', function () {
    $ctx = crearContextoTransicion();

    $traspaso = crearTraspasoTransicion($ctx, 30);

    $this->actingAs($ctx['usuario'])
        ->withSession(['active_sucursal_id' => $ctx['origen']->id])
        ->post(route('traspasos.enviar', $traspaso))
        ->assertRedirect();

    $this->actingAs($ctx['usuario'])
        ->withSession(['active_sucursal_id' => $ctx['destino']->id])
        ->post(route('traspasos.rechazar', $traspaso), ['motivo_respuesta' => 'Sin espacio'])
        ->assertRedirect();

    expect($traspaso->fresh()->estado)->toBe('rechazado')
        ->and(stockTransicion($ctx['origen']->id, $ctx['lote']->id))->toBe(100)
        ->and($ctx['lote']->fresh()->stock_lote)->toBe(100)
        ->and(stockTransicion($ctx['destino']->id, $ctx['lote']->id))->toBe(20);
});

test('se respetan sucursales y permisos al enviar y recibir', function () {
    $ctx = crearContextoTransicion();

    $rolCajero = Rol::firstOrCreate(['tipo_rol' => 'Cajero'], ['descripcion' => 'Ventas']);
    $ajeno = User::factory()->create(['id_rol' => $rolCajero->id, 'id_sucursal' => $ctx['destino']->id]);

    foreach (['Entradas de almacén', 'Alertas'] as $nombreModulo) {
        $modulo = Modulo::firstOrCreate(['nombre_modulo' => $nombreModulo]);
        PermisoActivado::create([
            'id_modulo' => $modulo->id,
            'id_usuario' => $ajeno->id,
            'puede_ver' => true,
            'puede_crear' => false,
            'puede_editar' => true,
            'puede_borrar' => false,
        ]);
    }

    $traspaso = crearTraspasoTransicion($ctx, 30);

    $envioAjeno = $this->actingAs($ajeno)
        ->withSession(['active_sucursal_id' => $ctx['destino']->id])
        ->post(route('traspasos.enviar', $traspaso));
    $envioAjeno->assertRedirect();
    $envioAjeno->assertSessionHas('error');

    $this->actingAs($ctx['usuario'])
        ->withSession(['active_sucursal_id' => $ctx['origen']->id])
        ->post(route('traspasos.enviar', $traspaso))
        ->assertRedirect();

    $recepcionAjena = $this->actingAs($ajeno)
        ->withSession(['active_sucursal_id' => $ctx['origen']->id])
        ->post(route('traspasos.aceptar', $traspaso));
    $recepcionAjena->assertRedirect();
    $recepcionAjena->assertSessionHas('error');

    expect($traspaso->fresh()->estado)->toBe('enviado')
        ->and(stockTransicion($ctx['origen']->id, $ctx['lote']->id))->toBe(70)
        ->and(stockTransicion($ctx['destino']->id, $ctx['lote']->id))->toBe(20);
});

test('aceptar exige envio previo del origen', function () {
    $ctx = crearContextoTransicion();

    $traspaso = crearTraspasoTransicion($ctx, 30);

    $respuesta = $this->actingAs($ctx['usuario'])
        ->withSession(['active_sucursal_id' => $ctx['destino']->id])
        ->post(route('traspasos.aceptar', $traspaso));

    $respuesta->assertRedirect();
    $respuesta->assertSessionHas('error');

    expect($traspaso->fresh()->estado)->toBe('pendiente')
        ->and(stockTransicion($ctx['origen']->id, $ctx['lote']->id))->toBe(100)
        ->and(stockTransicion($ctx['destino']->id, $ctx['lote']->id))->toBe(20);
});
