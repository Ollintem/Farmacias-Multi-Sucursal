<?php

use App\Models\Lote;
use App\Models\PresentacionProducto;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;

/**
 * Crea la sucursal, el usuario SuperAdmin, el producto y la presentación
 * compartidos por los tests de proveedor en lotes.
 *
 * @return array{sucursal: Sucursal, usuario: User, producto: Producto, caja: PresentacionProducto}
 */
function escenarioLoteProveedor(): array
{
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Lotes Proveedor',
        'direccion' => 'Calle Proveedor 1',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $rol = Rol::firstOrCreate(['tipo_rol' => 'SuperAdmin'], ['descripcion' => 'Acceso total']);
    $usuario = User::factory()->create(['id_rol' => $rol->id, 'id_sucursal' => $sucursal->id]);

    $caja = PresentacionProducto::create(['presentacion' => 'Caja', 'descripcion' => '']);

    $producto = Producto::create([
        'codigo_barras' => '7500000000501',
        'nombre_producto' => 'Producto Proveedor Demo',
        'descripcion' => '',
        'stock' => 0,
        'precio' => 10.0,
        'id_presentacion' => $caja->id,
        'es_controlado' => false,
        'es_activo' => true,
    ]);

    $producto->presentacionesPrecio()->create([
        'id_presentacion' => $caja->id,
        'unidades' => 10,
        'precio_presentacion' => 22.5,
    ]);

    return ['sucursal' => $sucursal, 'usuario' => $usuario, 'producto' => $producto, 'caja' => $caja];
}

/**
 * Crea un proveedor con el nombre indicado.
 */
function proveedorLotePrueba(string $nombre): Proveedor
{
    return Proveedor::create([
        'nombre_proveedor' => $nombre,
        'direccion' => 'Calle 77',
        'telefono' => '5551112222',
        'correo' => 'contacto@demo.mx',
        'unidad_entrega' => 'caja',
    ]);
}

test('el alta de lote guarda el proveedor elegido', function () {
    $escenario = escenarioLoteProveedor();
    $proveedor = proveedorLotePrueba('Farmacéutica Norte');

    $this->actingAs($escenario['usuario'])
        ->withSession(['active_sucursal_id' => $escenario['sucursal']->id])
        ->post(route('lotes.store'), [
            'folio' => 'L-PROV-001',
            'id_proveedor' => $proveedor->id,
            'sucursal' => $escenario['sucursal']->id,
            'entregado_en' => '2026-10-01',
            'fecha_caducidad' => '2027-10-01',
            'id_producto' => $escenario['producto']->id,
            'id_presentacion' => $escenario['caja']->id,
            'stock' => 12,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('lotes', [
        'folio' => 'L-PROV-001',
        'id_proveedor' => $proveedor->id,
    ]);
});

test('la tabla muestra el proveedor directo del lote y el respaldo sin proveedor', function () {
    $escenario = escenarioLoteProveedor();
    $proveedor = proveedorLotePrueba('Distribuidora Central');

    Lote::create([
        'folio' => 'L-PROV-TABLA',
        'stock_lote' => 8,
        'id_producto' => $escenario['producto']->id,
        'id_proveedor' => $proveedor->id,
        'fecha_caducidad' => '2027-06-30',
    ]);

    Lote::create([
        'folio' => 'L-PROV-NADA',
        'stock_lote' => 3,
        'id_producto' => $escenario['producto']->id,
        'fecha_caducidad' => '2027-06-30',
    ]);

    $pagina = $this->actingAs($escenario['usuario'])
        ->withSession(['active_sucursal_id' => $escenario['sucursal']->id])
        ->get(route('lotes.index'))
        ->assertOk()
        ->getContent();

    expect($pagina)
        ->toContain('Distribuidora Central')
        ->toContain('Sin proveedor');
});

test('la busqueda por nombre de proveedor encuentra el lote directo', function () {
    $escenario = escenarioLoteProveedor();
    $proveedor = proveedorLotePrueba('Cafarma Especializada');

    Lote::create([
        'folio' => 'L-PROV-BUSCA',
        'stock_lote' => 5,
        'id_producto' => $escenario['producto']->id,
        'id_proveedor' => $proveedor->id,
        'fecha_caducidad' => '2027-06-30',
    ]);

    Lote::create([
        'folio' => 'L-PROV-OTRO',
        'stock_lote' => 5,
        'id_producto' => $escenario['producto']->id,
        'fecha_caducidad' => '2027-06-30',
    ]);

    $this->actingAs($escenario['usuario'])
        ->withSession(['active_sucursal_id' => $escenario['sucursal']->id])
        ->get(route('lotes.index', ['buscar' => 'Cafarma']))
        ->assertOk()
        ->assertSee('L-PROV-BUSCA')
        ->assertDontSee('L-PROV-OTRO');
});
