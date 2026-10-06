<?php

use App\Models\Inventario;
use App\Models\Lote;
use App\Models\Modulo;
use App\Models\PermisoActivado;
use App\Models\PresentacionProducto;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;

/**
 * Crea la sucursal, el usuario SuperAdmin y el producto compartidos
 * por los tests de anulación de lote.
 *
 * @return array{sucursal: Sucursal, usuario: User, producto: Producto}
 */
function escenarioLoteAnulacion(): array
{
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Lotes Anulacion',
        'direccion' => 'Calle Anulacion 1',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $rol = Rol::firstOrCreate(['tipo_rol' => 'SuperAdmin'], ['descripcion' => 'Acceso total']);
    $usuario = User::factory()->create(['id_rol' => $rol->id, 'id_sucursal' => $sucursal->id]);

    $caja = PresentacionProducto::create(['presentacion' => 'Caja', 'descripcion' => '']);

    $producto = Producto::create([
        'codigo_barras' => '7500000000521',
        'nombre_producto' => 'Producto Anulacion Demo',
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
 * Crea un lote con su fila de inventario en la sucursal indicada.
 *
 * Entrada: sucursal, producto, folio y existencia actual del lote.
 * Salida: el lote creado.
 */
function lotePruebaAnulacion(Sucursal $sucursal, Producto $producto, string $folio, int $stock): Lote
{
    $lote = Lote::create([
        'folio' => $folio,
        'stock_lote' => 10,
        'id_producto' => $producto->id,
        'fecha_caducidad' => '2027-09-30',
    ]);

    Inventario::create([
        'id_sucursal' => $sucursal->id,
        'id_lote' => $lote->id,
        'stock' => $stock,
    ]);

    return $lote;
}

test('anula un lote sin existencias y lo muestra como anulado', function () {
    $escenario = escenarioLoteAnulacion();
    $lote = lotePruebaAnulacion($escenario['sucursal'], $escenario['producto'], 'L-ANU-OK', 0);

    $this->actingAs($escenario['usuario'])
        ->withSession(['active_sucursal_id' => $escenario['sucursal']->id])
        ->post(route('lotes.anular', $lote))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($lote->refresh()->anulado_en)->not->toBeNull();

    // Cuenta en la tarjeta Caducados aunque su fecha siga vigente.
    $this->actingAs($escenario['usuario'])
        ->withSession(['active_sucursal_id' => $escenario['sucursal']->id])
        ->get(route('lotes.index'))
        ->assertOk()
        ->assertSee('Anulado')
        ->assertViewHas('caducados', 1)
        ->assertViewHas('vigentes', 0);
});

test('no anula un lote con existencias en otra sucursal', function () {
    $escenario = escenarioLoteAnulacion();

    $otraSucursal = Sucursal::create([
        'nombre_sucursal' => 'Lotes Anulacion B',
        'direccion' => 'Calle Anulacion 2',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $lote = Lote::create([
        'folio' => 'L-ANU-OTRA',
        'stock_lote' => 5,
        'id_producto' => $escenario['producto']->id,
        'fecha_caducidad' => '2027-09-30',
    ]);

    Inventario::create([
        'id_sucursal' => $otraSucursal->id,
        'id_lote' => $lote->id,
        'stock' => 5,
    ]);

    $this->actingAs($escenario['usuario'])
        ->withSession(['active_sucursal_id' => $escenario['sucursal']->id])
        ->post(route('lotes.anular', $lote))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect($lote->refresh()->anulado_en)->toBeNull();
});

test('no permite anular dos veces el mismo lote', function () {
    $escenario = escenarioLoteAnulacion();
    $lote = lotePruebaAnulacion($escenario['sucursal'], $escenario['producto'], 'L-ANU-DOS', 0);

    $this->actingAs($escenario['usuario'])
        ->withSession(['active_sucursal_id' => $escenario['sucursal']->id])
        ->post(route('lotes.anular', $lote))
        ->assertSessionHas('success');

    $primeraAnulacion = $lote->refresh()->anulado_en;

    $this->actingAs($escenario['usuario'])
        ->withSession(['active_sucursal_id' => $escenario['sucursal']->id])
        ->post(route('lotes.anular', $lote))
        ->assertSessionHas('error');

    expect($lote->refresh()->anulado_en->toDateTimeString())->toBe($primeraAnulacion->toDateTimeString());
});

test('respeta el permiso eliminar del modulo lotes', function () {
    $escenario = escenarioLoteAnulacion();
    $lote = lotePruebaAnulacion($escenario['sucursal'], $escenario['producto'], 'L-ANU-PERMISO', 0);

    $rol = Rol::create(['tipo_rol' => 'Almacen', 'descripcion' => 'Solo almacén']);
    $usuario = User::factory()->create(['id_rol' => $rol->id, 'id_sucursal' => $escenario['sucursal']->id]);
    $modulo = Modulo::firstOrCreate(['nombre_modulo' => 'Lotes y caducidades']);
    PermisoActivado::create([
        'id_modulo' => $modulo->id,
        'id_usuario' => $usuario->id,
        'puede_ver' => true,
        'puede_editar' => true,
        'puede_borrar' => false,
    ]);

    $this->actingAs($usuario)
        ->withSession(['active_sucursal_id' => $escenario['sucursal']->id])
        ->post(route('lotes.anular', $lote))
        ->assertForbidden();

    expect($lote->refresh()->anulado_en)->toBeNull();
});

test('el boton anular aparece solo en lotes sin existencias', function () {
    $escenario = escenarioLoteAnulacion();
    $loteSinExistencias = lotePruebaAnulacion($escenario['sucursal'], $escenario['producto'], 'L-ANU-VISTA-SIN', 0);
    $loteConExistencias = lotePruebaAnulacion($escenario['sucursal'], $escenario['producto'], 'L-ANU-VISTA-CON', 10);

    $pagina = $this->actingAs($escenario['usuario'])
        ->withSession(['active_sucursal_id' => $escenario['sucursal']->id])
        ->get(route('lotes.index'))
        ->assertOk()
        ->getContent();

    expect($pagina)
        ->toContain(route('lotes.anular', $loteSinExistencias->id))
        ->not->toContain(route('lotes.anular', $loteConExistencias->id));
});
