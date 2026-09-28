<?php

use App\Models\Categoria;
use App\Models\Lote;
use App\Models\PresentacionProducto;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;

function contextoBusquedaTiempoReal(object $test): array
{
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Busqueda',
        'direccion' => 'Av. Busqueda 1',
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

    $categoria = Categoria::create(['nombre' => 'Analgesicos']);
    $caja = PresentacionProducto::create(['presentacion' => 'Caja', 'descripcion' => '']);

    $test->actingAs($user);
    session(['active_sucursal_id' => $sucursal->id]);

    return [$sucursal, $categoria, $caja];
}

function productoBusquedaTiempoReal(Categoria $categoria, PresentacionProducto $caja): Producto
{
    return Producto::create([
        'codigo_barras' => '7501112223345',
        'nombre_producto' => 'Paracetamol 500mg',
        'descripcion' => '',
        'stock' => 0,
        'precio' => null,
        'id_presentacion' => $caja->id,
        'id_categoria' => $categoria->id,
        'es_controlado' => false,
        'es_activo' => true,
    ]);
}

test('la pestana productos marca las filas para el filtrado en tiempo real', function () {
    [$sucursal, $categoria, $caja] = contextoBusquedaTiempoReal($this);
    productoBusquedaTiempoReal($categoria, $caja);

    $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('inventario.productos'))
        ->assertOk()
        ->assertSee('function buscadorTabla', false)
        ->assertSee('x-data="buscadorTabla()"', false)
        ->assertSee('x-effect="filtrar($el)"', false)
        ->assertSee('@input.debounce.200ms="texto = $event.target.value"', false)
        ->assertSee('data-buscar="7501112223345 Paracetamol 500mg"', false)
        ->assertSee('data-vacio', false);
});

test('la pestana stock marca las filas y deja un solo buscador', function () {
    [$sucursal, $categoria, $caja] = contextoBusquedaTiempoReal($this);
    productoBusquedaTiempoReal($categoria, $caja);

    $response = $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('inventario.stock'));

    $response->assertOk()
        ->assertSee('x-data="buscadorTabla()"', false)
        ->assertSee('@input.debounce.200ms="texto = $event.target.value"', false)
        ->assertSee('data-buscar="7501112223345 Paracetamol 500mg"', false)
        ->assertSee('data-vacio', false);

    expect(substr_count($response->getContent(), 'name="buscar"'))->toBe(1);
});

test('la pestana de lotes marca las filas con folio producto y proveedor', function () {
    [$sucursal, $categoria, $caja] = contextoBusquedaTiempoReal($this);
    $producto = productoBusquedaTiempoReal($categoria, $caja);

    Lote::create([
        'folio' => 'L-9001',
        'stock_lote' => 4,
        'id_producto' => $producto->id,
        'fecha_caducidad' => '2027-03-01',
    ]);

    $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('lotes.index'))
        ->assertOk()
        ->assertSee('x-data="buscadorTabla()"', false)
        ->assertSee('@input.debounce.200ms="texto = $event.target.value"', false)
        ->assertSee('data-buscar="L-9001 Paracetamol 500mg Sin proveedor"', false)
        ->assertSee('data-vacio', false);
});
