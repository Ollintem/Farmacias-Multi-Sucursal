<?php

use App\Models\Lote;
use App\Models\PresentacionProducto;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Sucursal;

test('el inventario puede filtrar por sucursal y guardar un producto ligado a la base de datos', function () {
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Centro',
        'direccion' => 'Av. Central 123',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $proveedor = Proveedor::create([
        'nombre_proveedor' => 'Distribuidora Demo',
        'direccion' => 'Calle del Sol 99',
        'unidad_entrega' => 'Caja',
        'telefono' => '5550001111',
        'correo' => 'demo@farmacia.mx',
    ]);

    $lote = Lote::create([
        'folio' => 'LOT-001',
        'id_proveedor' => $proveedor->id,
        'entregado_en' => now(),
        'fecha_caducidad' => now()->addYear(),
    ]);

    $presentacion = PresentacionProducto::create([
        'presentacion' => 'Caja',
        'descripcion' => 'Presentación estándar',
    ]);

    $response = $this->get(route('inventario.index', ['sucursal' => $sucursal->id]));
    $response->assertOk();
    $response->assertSee('Sucursal Centro');

    $response = $this->post(route('inventario.store'), [
        'sucursal' => $sucursal->id,
        'codigo_barras' => '7501234567890',
        'nombre_producto' => 'Vitamina C Plus',
        'descripcion' => 'Suplemento vitamínico',
        'stock' => 45,
        'precio' => 128.5,
        'id_lote' => $lote->id,
        'id_presentacion' => $presentacion->id,
    ]);

    $response->assertRedirect(route('inventario.index', ['sucursal' => $sucursal->id]));
    $this->assertDatabaseHas('productos', [
        'codigo_barras' => '7501234567890',
        'nombre_producto' => 'Vitamina C Plus',
    ]);

    $producto = Producto::where('codigo_barras', '7501234567890')->firstOrFail();
    $this->assertDatabaseHas('inventario', [
        'id_producto' => $producto->id,
        'id_sucursal' => $sucursal->id,
    ]);
});
