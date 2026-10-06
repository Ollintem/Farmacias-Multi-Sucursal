<?php

use App\Models\Lote;
use App\Models\Modulo;
use App\Models\PermisoActivado;
use App\Models\PresentacionProducto;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;
use Carbon\Carbon;

/**
 * Crea la sucursal, el usuario SuperAdmin y el producto compartidos
 * por los tests de edición de lote.
 *
 * @return array{sucursal: Sucursal, usuario: User, producto: Producto}
 */
function escenarioLoteEdicion(): array
{
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Lotes Edicion',
        'direccion' => 'Calle Edicion 1',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $rol = Rol::firstOrCreate(['tipo_rol' => 'SuperAdmin'], ['descripcion' => 'Acceso total']);
    $usuario = User::factory()->create(['id_rol' => $rol->id, 'id_sucursal' => $sucursal->id]);

    $caja = PresentacionProducto::create(['presentacion' => 'Caja', 'descripcion' => '']);

    $producto = Producto::create([
        'codigo_barras' => '7500000000511',
        'nombre_producto' => 'Producto Edicion Demo',
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
 * Crea un lote editable de fecha de entrega 2026-01-10 y caducidad 2027-01-10.
 */
function lotePruebaEdicion(Producto $producto, Proveedor $proveedor): Lote
{
    return Lote::create([
        'folio' => 'L-EDIT-001',
        'stock_lote' => 20,
        'id_producto' => $producto->id,
        'id_proveedor' => $proveedor->id,
        'entregado_en' => '2026-01-10',
        'fecha_caducidad' => '2027-01-10',
    ]);
}

/**
 * Crea un proveedor con el nombre indicado para la edición de lotes.
 */
function proveedorPruebaEdicion(string $nombre): Proveedor
{
    return Proveedor::create([
        'nombre_proveedor' => $nombre,
        'direccion' => 'Calle 88',
        'telefono' => '5553334444',
        'correo' => 'edicion@demo.mx',
        'unidad_entrega' => 'caja',
    ]);
}

test('el formulario de edicion muestra los datos actuales del lote', function () {
    $escenario = escenarioLoteEdicion();
    $proveedor = proveedorPruebaEdicion('Proveedor Edicion');
    $lote = lotePruebaEdicion($escenario['producto'], $proveedor);

    $this->actingAs($escenario['usuario'])
        ->withSession(['active_sucursal_id' => $escenario['sucursal']->id])
        ->get(route('lotes.edit', $lote))
        ->assertOk()
        ->assertSee('L-EDIT-001')
        ->assertSee('Producto Edicion Demo')
        ->assertSee('value="2027-01-10"', false)
        ->assertSee('Proveedor Edicion')
        ->assertSee('Existencia actual');
});

test('actualiza la caducidad y el proveedor sin tocar las existencias', function () {
    $escenario = escenarioLoteEdicion();
    $proveedor = proveedorPruebaEdicion('Proveedor Viejo');
    $lote = lotePruebaEdicion($escenario['producto'], $proveedor);
    $nuevoProveedor = proveedorPruebaEdicion('Proveedor Nuevo');

    $this->actingAs($escenario['usuario'])
        ->withSession(['active_sucursal_id' => $escenario['sucursal']->id])
        ->put(route('lotes.update', $lote), [
            'fecha_caducidad' => '2027-05-20',
            'id_proveedor' => $nuevoProveedor->id,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $lote->refresh();

    expect($lote->fecha_caducidad->format('Y-m-d'))->toBe('2027-05-20')
        // fecha_de_caducidad tiene accessor propio y devuelve crudo (string).
        ->and(Carbon::parse($lote->fecha_de_caducidad)->format('Y-m-d'))->toBe('2027-05-20')
        ->and((int) $lote->id_proveedor)->toBe($nuevoProveedor->id)
        ->and((int) $lote->stock_lote)->toBe(20);
});

test('rechaza una caducidad anterior a la fecha de entrega', function () {
    $escenario = escenarioLoteEdicion();
    $proveedor = proveedorPruebaEdicion('Proveedor Valida');
    $lote = lotePruebaEdicion($escenario['producto'], $proveedor);

    $this->actingAs($escenario['usuario'])
        ->withSession(['active_sucursal_id' => $escenario['sucursal']->id])
        ->put(route('lotes.update', $lote), [
            'fecha_caducidad' => '2025-12-31',
            'id_proveedor' => '',
        ])
        ->assertSessionHasErrors('fecha_caducidad');

    $lote->refresh();

    expect($lote->fecha_caducidad->format('Y-m-d'))->toBe('2027-01-10')
        ->and((int) $lote->id_proveedor)->toBe($proveedor->id);
});

test('respeta el permiso editar del modulo lotes', function () {
    $escenario = escenarioLoteEdicion();
    $proveedor = proveedorPruebaEdicion('Proveedor Permiso');
    $lote = lotePruebaEdicion($escenario['producto'], $proveedor);

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
        ->get(route('lotes.edit', $lote))
        ->assertForbidden();

    $this->actingAs($usuario)
        ->withSession(['active_sucursal_id' => $escenario['sucursal']->id])
        ->put(route('lotes.update', $lote), [
            'fecha_caducidad' => '2027-08-01',
            'id_proveedor' => $proveedor->id,
        ])
        ->assertForbidden();

    expect($lote->refresh()->fecha_caducidad->format('Y-m-d'))->toBe('2027-01-10');
});
