<?php

use App\Models\Inventario;
use App\Models\Lote;
use App\Models\Merma;
use App\Models\Modulo;
use App\Models\PermisoActivado;
use App\Models\PresentacionProducto;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;
use App\Support\AlertasResumen;

/**
 * Crea la sucursal activa, el usuario SuperAdmin y el producto
 * compartidos por los tests de merma.
 *
 * @return array{sucursal: Sucursal, usuario: User, producto: Producto}
 */
function escenarioMerma(): array
{
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Merma Norte',
        'direccion' => 'Calle 33',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $rol = Rol::firstOrCreate(['tipo_rol' => 'SuperAdmin'], ['descripcion' => 'Acceso total']);
    $usuario = User::factory()->create(['id_rol' => $rol->id, 'id_sucursal' => $sucursal->id]);

    $caja = PresentacionProducto::create(['presentacion' => 'Caja', 'descripcion' => '']);

    $producto = Producto::create([
        'codigo_barras' => '7500000000400',
        'nombre_producto' => 'Producto Prueba Merma',
        'descripcion' => '',
        'stock' => 0,
        'precio' => 10.0,
        'id_presentacion' => $caja->id,
        'es_controlado' => false,
        'es_activo' => true,
    ]);

    return ['sucursal' => $sucursal, 'usuario' => $usuario, 'producto' => $producto];
}

/**
 * Crea una sucursal adicional para escenarios con dos sucursales.
 */
function sucursalMerma(string $nombre): Sucursal
{
    return Sucursal::create([
        'nombre_sucursal' => $nombre,
        'direccion' => 'Calle 44',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);
}

/**
 * Crea un lote con su fila de inventario en la sucursal indicada
 * y refresca el espejo del producto.
 *
 * Entrada: sucursal, producto, folio, unidades en stock y fecha de caducidad.
 * Salida: el lote creado.
 */
function loteMerma(Sucursal $sucursal, Producto $producto, string $folio, int $stock, string $fecha = '2027-06-30'): Lote
{
    $lote = Lote::create([
        'folio' => $folio,
        'stock_lote' => $stock,
        'id_producto' => $producto->id,
        'fecha_caducidad' => $fecha,
    ]);

    Inventario::create([
        'id_sucursal' => $sucursal->id,
        'id_lote' => $lote->id,
        'stock' => $stock,
    ]);

    Inventario::reflejarStockGlobal($producto->id);

    return $lote;
}

test('da de baja existencia y deja trazabilidad de la merma', function () {
    $escenario = escenarioMerma();
    $lote = loteMerma($escenario['sucursal'], $escenario['producto'], 'L-MERMA-OK', 10);

    $respuesta = $this->actingAs($escenario['usuario'])
        ->withSession(['active_sucursal_id' => $escenario['sucursal']->id])
        ->post(route('lotes.merma'), [
            'lote_id' => $lote->id,
            'sucursal' => $escenario['sucursal']->id,
            'cantidad' => 4,
            'motivo' => 'caducado',
            'nota' => 'Fuera de la fecha de consumo.',
        ]);

    $respuesta->assertRedirect()->assertSessionHas('success');

    expect(Inventario::where('id_lote', $lote->id)->first()->stock)->toBe(6)
        ->and((int) $escenario['producto']->fresh()->stock)->toBe(6)
        ->and((int) $lote->fresh()->stock_lote)->toBe(10)
        ->and(Merma::count())->toBe(1);

    $merma = Merma::first();
    expect($merma->id_lote)->toBe($lote->id)
        ->and($merma->id_sucursal)->toBe($escenario['sucursal']->id)
        ->and($merma->id_usuario)->toBe($escenario['usuario']->id)
        ->and($merma->cantidad)->toBe(4)
        ->and($merma->motivo)->toBe('caducado')
        ->and($merma->nota)->toBe('Fuera de la fecha de consumo.');
});

test('rechaza una cantidad mayor al restante sin tocar el stock', function () {
    $escenario = escenarioMerma();
    $lote = loteMerma($escenario['sucursal'], $escenario['producto'], 'L-MERMA-EXCESO', 5);

    $this->actingAs($escenario['usuario'])
        ->withSession(['active_sucursal_id' => $escenario['sucursal']->id])
        ->post(route('lotes.merma'), [
            'lote_id' => $lote->id,
            'sucursal' => $escenario['sucursal']->id,
            'cantidad' => 6,
            'motivo' => 'caducado',
        ])
        ->assertSessionHasErrors('cantidad');

    expect(Inventario::where('id_lote', $lote->id)->first()->stock)->toBe(5)
        ->and((int) $escenario['producto']->fresh()->stock)->toBe(5)
        ->and(Merma::count())->toBe(0);
});

test('rechaza la merma si el lote no tiene fila en la sucursal', function () {
    $escenario = escenarioMerma();
    $otraSucursal = sucursalMerma('Merma Otra');
    $lote = loteMerma($otraSucursal, $escenario['producto'], 'L-MERMA-OTRA', 8);

    $this->actingAs($escenario['usuario'])
        ->withSession(['active_sucursal_id' => $escenario['sucursal']->id])
        ->post(route('lotes.merma'), [
            'lote_id' => $lote->id,
            'sucursal' => $escenario['sucursal']->id,
            'cantidad' => 2,
            'motivo' => 'danado',
        ])
        ->assertSessionHasErrors('cantidad');

    expect(Inventario::where('id_lote', $lote->id)->first()->stock)->toBe(8)
        ->and(Merma::count())->toBe(0);
});

test('respeta el permiso editar del modulo lotes', function () {
    $escenario = escenarioMerma();
    $lote = loteMerma($escenario['sucursal'], $escenario['producto'], 'L-MERMA-PERMISO', 10);

    $rol = Rol::create(['tipo_rol' => 'Almacen', 'descripcion' => 'Solo almacén']);
    $usuario = User::factory()->create(['id_rol' => $rol->id, 'id_sucursal' => $escenario['sucursal']->id]);
    $modulo = Modulo::firstOrCreate(['nombre_modulo' => 'Lotes y caducidades']);
    PermisoActivado::create([
        'id_modulo' => $modulo->id,
        'id_usuario' => $usuario->id,
        'puede_ver' => true,
        'puede_editar' => false,
    ]);

    $this->actingAs($usuario)
        ->withSession(['active_sucursal_id' => $escenario['sucursal']->id])
        ->post(route('lotes.merma'), [
            'lote_id' => $lote->id,
            'sucursal' => $escenario['sucursal']->id,
            'cantidad' => 1,
            'motivo' => 'caducado',
        ])
        ->assertForbidden();

    expect(Merma::count())->toBe(0)
        ->and(Inventario::where('id_lote', $lote->id)->first()->stock)->toBe(10);
});

test('el motivo otro exige nota', function () {
    $escenario = escenarioMerma();
    $lote = loteMerma($escenario['sucursal'], $escenario['producto'], 'L-MERMA-NOTA', 10);

    $this->actingAs($escenario['usuario'])
        ->withSession(['active_sucursal_id' => $escenario['sucursal']->id])
        ->post(route('lotes.merma'), [
            'lote_id' => $lote->id,
            'sucursal' => $escenario['sucursal']->id,
            'cantidad' => 1,
            'motivo' => 'otro',
        ])
        ->assertSessionHasErrors('nota');

    expect(Merma::count())->toBe(0);

    $this->actingAs($escenario['usuario'])
        ->withSession(['active_sucursal_id' => $escenario['sucursal']->id])
        ->post(route('lotes.merma'), [
            'lote_id' => $lote->id,
            'sucursal' => $escenario['sucursal']->id,
            'cantidad' => 1,
            'motivo' => 'otro',
            'nota' => 'Se rompió la cadena de frío.',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(Merma::count())->toBe(1)
        ->and(Merma::first()->motivo)->toBe('otro')
        ->and(Inventario::where('id_lote', $lote->id)->first()->stock)->toBe(9);
});

test('mermar a cero elimina la alerta de caducidad', function () {
    $escenario = escenarioMerma();
    $lote = loteMerma($escenario['sucursal'], $escenario['producto'], 'L-MERMA-ALERTA', 5, now()->addDays(10)->format('Y-m-d'));

    expect(AlertasResumen::counts($escenario['sucursal']->id)['rojos'])->toBe(1);

    $this->actingAs($escenario['usuario'])
        ->withSession(['active_sucursal_id' => $escenario['sucursal']->id])
        ->post(route('lotes.merma'), [
            'lote_id' => $lote->id,
            'sucursal' => $escenario['sucursal']->id,
            'cantidad' => 5,
            'motivo' => 'caducado',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(AlertasResumen::counts($escenario['sucursal']->id)['rojos'])->toBe(0);
});

test('la pagina de lotes ofrece la baja solo con existencia', function () {
    $escenario = escenarioMerma();
    $loteConStock = loteMerma($escenario['sucursal'], $escenario['producto'], 'L-MERMA-VISTA-CON', 10);
    $loteSinStock = loteMerma($escenario['sucursal'], $escenario['producto'], 'L-MERMA-VISTA-SIN', 0);

    $pagina = $this->actingAs($escenario['usuario'])
        ->withSession(['active_sucursal_id' => $escenario['sucursal']->id])
        ->get(route('lotes.index'))
        ->assertOk()
        ->getContent();

    expect($pagina)
        ->toContain(route('lotes.merma'))
        ->toContain('data-lote="'.$loteConStock->id.'"')
        ->not->toContain('data-lote="'.$loteSinStock->id.'"');
});
