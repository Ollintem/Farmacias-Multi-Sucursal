<?php

use App\Models\Categoria;
use App\Models\Inventario;
use App\Models\PresentacionProducto;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;

function crearUsuarioDePrueba(Sucursal $sucursal): User
{
    $rol = Rol::firstOrCreate(
        ['tipo_rol' => 'SuperAdmin'],
        ['descripcion' => 'Acceso total']
    );

    return User::factory()->create([
        'id_sucursal' => $sucursal->id,
        'id_rol' => $rol->id,
    ]);
}

test('el formulario de creacion carga con categorias y presentaciones', function () {
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Form',
        'direccion' => 'Av. Form 1',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    Categoria::create(['nombre' => 'Analgésicos']);
    PresentacionProducto::create(['presentacion' => 'Caja', 'descripcion' => '']);
    PresentacionProducto::create(['presentacion' => 'Blíster', 'descripcion' => '']);

    $this->actingAs(crearUsuarioDePrueba($sucursal));

    $response = $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('inventario.create'));

    $response->assertOk()
        ->assertSee('nombre_producto')
        ->assertSee('Analgésicos')
        ->assertSee('Agregar presentación')
        ->assertSee('vender_por_unidad')
        ->assertSee('catalogoPresentaciones')
        ->assertDontSee('Stock inicial');
});

test('los selectores de categoria y presentacion muestran la accion de crear nueva entrada', function () {
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Dropdown',
        'direccion' => 'Av. Dropdown 1',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    Categoria::create(['nombre' => 'Analgésicos']);
    PresentacionProducto::create(['presentacion' => 'Caja', 'descripcion' => '']);
    PresentacionProducto::create(['presentacion' => 'Blíster', 'descripcion' => '']);

    $this->actingAs(crearUsuarioDePrueba($sucursal));

    $contenido = $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('inventario.create'))
        ->assertOk()
        ->getContent();

    expect($contenido)
        ->toContain('<input type="hidden" name="id_categoria"')
        ->toContain('Agregar nueva categoría')
        ->toContain('Agregar nueva presentación')
        ->toContain('x-on:click.capture.window')
        ->toContain('x-effect="seguirDesplegable()"')
        ->toContain('role="listbox"')
        ->toContain('fixed z-20')
        ->toContain('x-transition.opacity')
        ->toContain('class="relative" data-dropdown')
        ->not->toContain('class="farma-pick-scroll mt-2"')
        ->toContain("abrirModal('categoria')")
        ->toContain("abrirModal('presentacion'")
        ->not->toContain('value="__nueva"')
        ->not->toContain('chequearNuevaCategoria')
        ->not->toContain('insertBefore');
});

test('el inventario guarda un producto con categoria y presentaciones en la sucursal activa', function () {
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Centro',
        'direccion' => 'Av. Central 123',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $categoria = Categoria::create(['nombre' => 'Analgésicos']);
    $caja = PresentacionProducto::create(['presentacion' => 'Caja', 'descripcion' => '']);
    $blister = PresentacionProducto::create(['presentacion' => 'Blíster', 'descripcion' => '']);

    $user = crearUsuarioDePrueba($sucursal);
    $this->actingAs($user);

    $response = $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('inventario.stock'));

    $response->assertOk();
    $response->assertSee('Sucursal Centro');

    $response = $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->post(route('inventario.store'), [
            'codigo_barras' => '7501234567890',
            'nombre_producto' => 'Paracetamol 500mg',
            'id_categoria' => $categoria->id,
            'descripcion' => 'Analgésico común',
            'vender_por_unidad' => '1',
            'precio' => 2.5,
            'es_controlado' => '0',
            'presentaciones' => [
                ['id_presentacion' => $caja->id, 'unidades' => 10, 'precio' => 22.5],
                ['id_presentacion' => $blister->id, 'unidades' => 10, 'precio' => 3.5],
            ],
        ]);

    $response->assertRedirect(route('inventario.productos', ['sucursal' => $sucursal->id]));

    $producto = Producto::where('codigo_barras', '7501234567890')->firstOrFail();

    $this->assertDatabaseHas('productos', [
        'id' => $producto->id,
        'nombre_producto' => 'Paracetamol 500mg',
        'id_categoria' => $categoria->id,
        'precio' => 2.5,
        'stock' => 0,
    ]);

    // El alta de producto no crea filas de inventario: el stock de la sucursal
    // llega después con el registro de lotes (LotesController::store).
    expect(Inventario::count())->toBe(0);

    $this->assertDatabaseHas('presentacion_producto', [
        'producto' => $producto->id,
        'id_presentacion' => $caja->id,
        'unidades' => 10,
        'precio_presentacion' => 22.5,
    ]);

    $this->assertDatabaseHas('presentacion_producto', [
        'producto' => $producto->id,
        'id_presentacion' => $blister->id,
        'unidades' => 10,
        'precio_presentacion' => 3.5,
    ]);
});

test('un producto sin precio unitario guarda precio nulo y solo presentaciones', function () {
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Norte',
        'direccion' => 'Av. Norte 456',
        'hora_apertura' => '09:00',
        'hora_cierre' => '18:00',
    ]);

    $categoria = Categoria::create(['nombre' => 'Antibióticos']);
    $caja = PresentacionProducto::create(['presentacion' => 'Caja', 'descripcion' => '']);

    $user = crearUsuarioDePrueba($sucursal);
    $this->actingAs($user);

    $response = $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->post(route('inventario.store'), [
            'codigo_barras' => '7509876543210',
            'nombre_producto' => 'Amoxicilina 500mg',
            'id_categoria' => $categoria->id,
            'vender_por_unidad' => null,
            'es_controlado' => '1',
            'presentaciones' => [
                ['id_presentacion' => $caja->id, 'unidades' => 20, 'precio' => 150.0],
            ],
        ]);

    $response->assertRedirect(route('inventario.productos', ['sucursal' => $sucursal->id]));

    $this->assertDatabaseHas('productos', [
        'codigo_barras' => '7509876543210',
        'precio' => null,
        'es_controlado' => true,
    ]);
});

test('el store rechaza presentaciones duplicadas en el mismo producto', function () {
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Sur',
        'direccion' => 'Av. Sur 789',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $categoria = Categoria::create(['nombre' => 'Vitaminas']);
    $caja = PresentacionProducto::create(['presentacion' => 'Caja', 'descripcion' => '']);

    $user = crearUsuarioDePrueba($sucursal);
    $this->actingAs($user);

    $response = $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->from(route('inventario.create'))
        ->post(route('inventario.store'), [
            'codigo_barras' => '7501112223334',
            'nombre_producto' => 'Vitamina C',
            'id_categoria' => $categoria->id,
            'vender_por_unidad' => '1',
            'precio' => 10,
            'presentaciones' => [
                ['id_presentacion' => $caja->id, 'unidades' => 10, 'precio' => 90],
                ['id_presentacion' => $caja->id, 'unidades' => 5, 'precio' => 50],
            ],
        ]);

    $response->assertSessionHasErrors('presentaciones.1.id_presentacion');
    $this->assertDatabaseMissing('productos', ['codigo_barras' => '7501112223334']);
});

test('el store requiere precio solo cuando se habilita venta por unidad', function () {
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Test',
        'direccion' => 'Calle 1',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $categoria = Categoria::create(['nombre' => 'Respiratorios']);
    $caja = PresentacionProducto::create(['presentacion' => 'Caja', 'descripcion' => '']);

    $user = crearUsuarioDePrueba($sucursal);
    $this->actingAs($user);

    $response = $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->from(route('inventario.create'))
        ->post(route('inventario.store'), [
            'codigo_barras' => '7505556667778',
            'nombre_producto' => 'Salbutamol',
            'id_categoria' => $categoria->id,
            'vender_por_unidad' => '1',
            'presentaciones' => [
                ['id_presentacion' => $caja->id, 'unidades' => 2, 'precio' => 30],
            ],
        ]);

    $response->assertSessionHasErrors('precio');
});

test('las presentaciones no aceptan una sola unidad porque para eso esta vender por unidad', function () {
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Unidades',
        'direccion' => 'Calle 9',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $categoria = Categoria::create(['nombre' => 'Analgésicos']);
    $caja = PresentacionProducto::create(['presentacion' => 'Caja', 'descripcion' => '']);

    $this->actingAs(crearUsuarioDePrueba($sucursal));

    $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->from(route('inventario.create'))
        ->post(route('inventario.store'), [
            'codigo_barras' => '7505556667785',
            'nombre_producto' => 'Ibuprofeno 400mg',
            'id_categoria' => $categoria->id,
            'presentaciones' => [
                ['id_presentacion' => $caja->id, 'unidades' => 1, 'precio' => 30],
            ],
        ])
        ->assertSessionHasErrors('presentaciones.0.unidades');

    $this->assertDatabaseMissing('productos', ['codigo_barras' => '7505556667785']);
});

test('la fila principal del formulario queda fija en Caja y Caja no aparece en las demas filas', function () {
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Fila',
        'direccion' => 'Av. Fila 1',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    Categoria::create(['nombre' => 'Dermatologicos']);
    $caja = PresentacionProducto::create(['presentacion' => 'Caja', 'descripcion' => '']);
    PresentacionProducto::create(['presentacion' => 'Blister', 'descripcion' => '']);

    $this->actingAs(crearUsuarioDePrueba($sucursal));

    $response = $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('inventario.create'));

    $response->assertOk();
    $response->assertSee('presentacionCajaId: '.$caja->id, false);
    $response->assertSee('disabled aria-label="Presentación principal"', false);
    $response->assertSee('x-text="presentacionCajaNombre || \'Caja\'"', false);
    $response->assertDontSee('opacity-70">fija</span>', false);

    preg_match('/catalogoPresentaciones:\s*(\[[^\]]*\])/', $response->getContent(), $coincidencia);

    $catalogo = json_decode($coincidencia[1] ?? '[]', true);

    expect($catalogo)->not->toBeEmpty()
        ->and(collect($catalogo)->pluck('presentacion')->all())->not->toContain('Caja');
});

test('el formulario avisa si no hay presentacion Caja en el catalogo', function () {
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal SinCaja',
        'direccion' => 'Av. SinCaja 2',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    Categoria::create(['nombre' => 'Heridas']);
    PresentacionProducto::create(['presentacion' => 'Blister', 'descripcion' => '']);

    $this->actingAs(crearUsuarioDePrueba($sucursal));

    $response = $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('inventario.create'));

    $response->assertOk()->assertSee('No hay presentación «Caja» registrada');
});

test('el store fija la primera presentacion en Caja aunque se envie otra', function () {
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Fija',
        'direccion' => 'Av. Fija 3',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $categoria = Categoria::create(['nombre' => 'Gastro']);
    $caja = PresentacionProducto::create(['presentacion' => 'Caja', 'descripcion' => '']);
    $blister = PresentacionProducto::create(['presentacion' => 'Blister', 'descripcion' => '']);

    $this->actingAs(crearUsuarioDePrueba($sucursal));

    $response = $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->post(route('inventario.store'), [
            'codigo_barras' => '7504445556667',
            'nombre_producto' => 'Omeprazol 20mg',
            'id_categoria' => $categoria->id,
            'presentaciones' => [
                ['id_presentacion' => $blister->id, 'unidades' => 14, 'precio' => 45],
            ],
        ]);

    $response->assertRedirect(route('inventario.productos', ['sucursal' => $sucursal->id]));

    $producto = Producto::where('codigo_barras', '7504445556667')->firstOrFail();

    expect($producto->id_presentacion)->toBe($caja->id);

    $this->assertDatabaseHas('presentacion_producto', [
        'producto' => $producto->id,
        'id_presentacion' => $caja->id,
        'unidades' => 14,
        'precio_presentacion' => 45,
    ]);
});

test('el store bloquea el alta de producto si falta la presentacion Caja', function () {
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Bloqueo',
        'direccion' => 'Av. Bloqueo 4',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $categoria = Categoria::create(['nombre' => 'Respiratorio']);
    $blister = PresentacionProducto::create(['presentacion' => 'Blister', 'descripcion' => '']);

    $this->actingAs(crearUsuarioDePrueba($sucursal));

    $response = $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->from(route('inventario.create'))
        ->post(route('inventario.store'), [
            'codigo_barras' => '7507778889990',
            'nombre_producto' => 'Ibuprofeno 400mg',
            'id_categoria' => $categoria->id,
            'presentaciones' => [
                ['id_presentacion' => $blister->id, 'unidades' => 10, 'precio' => 60],
            ],
        ]);

    $response->assertSessionHasErrors('presentaciones');
    $this->assertDatabaseMissing('productos', ['codigo_barras' => '7507778889990']);
});

test('el formulario de producto incluye el lector de camara de codigos', function () {
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Lector',
        'direccion' => 'Av. Lector 5',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    Categoria::create(['nombre' => 'Vitaminas']);
    PresentacionProducto::create(['presentacion' => 'Caja', 'descripcion' => '']);

    $this->actingAs(crearUsuarioDePrueba($sucursal));

    $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('inventario.create'))
        ->assertOk()
        ->assertSee('id="scanner-focus-button"', false)
        ->assertSee('id="barcode-input"', false)
        ->assertSee('Escanear con cámara', false);
});

test('el modulo inventario abre productos por defecto y stock queda en su propia ruta', function () {
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Defecto',
        'direccion' => 'Av. Defecto 6',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $this->actingAs(crearUsuarioDePrueba($sucursal));

    $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('inventario.index'))
        ->assertRedirect(route('inventario.productos'));

    $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('inventario.index', ['sucursal' => $sucursal->id]))
        ->assertRedirect(route('inventario.productos', ['sucursal' => $sucursal->id]));

    $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('inventario.productos'))
        ->assertOk()
        ->assertSee('+ Agregar producto nuevo');

    $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('inventario.stock'))
        ->assertOk()
        ->assertSee('Stock crítico');
});
