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
        ->assertSee('La venta y distribución del producto se realizará según la presentación seleccionada.')
        ->assertSee('Al agregar un presentación la unidad se dejará de distribuir por unidades individuales. Lo hará por cajas y otras presentaciones derivadas')
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
            'es_controlado' => '0',
            'presentaciones' => [
                ['id_presentacion' => $caja->id, 'unidades' => 10, 'precio' => 22.5, 'stock_minimo' => 5, 'es_principal' => 1],
                ['id_presentacion' => $blister->id, 'unidades' => 10, 'precio' => 3.5, 'stock_minimo' => 9, 'es_principal' => 0],
            ],
        ]);

    $response->assertRedirect(route('inventario.productos', ['sucursal' => $sucursal->id]));

    $producto = Producto::where('codigo_barras', '7501234567890')->firstOrFail();

    $this->assertDatabaseHas('productos', [
        'id' => $producto->id,
        'nombre_producto' => 'Paracetamol 500mg',
        'id_categoria' => $categoria->id,
    ]);

    // El alta de producto no crea filas de inventario: el stock de la sucursal
    // llega después con el registro de lotes (LotesController::store).
    expect(Inventario::count())->toBe(0);

    $this->assertDatabaseHas('presentacion_producto', [
        'producto' => $producto->id,
        'id_presentacion' => $caja->id,
        'unidades' => 10,
        'precio_presentacion' => 22.5,
        'stock_minimo' => 5,
        'es_principal' => true,
    ]);

    $this->assertDatabaseHas('presentacion_producto', [
        'producto' => $producto->id,
        'id_presentacion' => $blister->id,
        'unidades' => 10,
        'precio_presentacion' => 3.5,
        'stock_minimo' => 0,
        'es_principal' => false,
    ]);
});

test('el store asigna la presentacion principal cuando no se marca ninguna', function () {
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
            'es_controlado' => '1',
            'presentaciones' => [
                ['id_presentacion' => $caja->id, 'unidades' => 20, 'precio' => 150.0],
            ],
        ]);

    $response->assertRedirect(route('inventario.productos', ['sucursal' => $sucursal->id]));

    $producto = Producto::where('codigo_barras', '7509876543210')->firstOrFail();

    expect($producto->es_controlado)->toBeTrue();

    $this->assertDatabaseHas('presentacion_producto', [
        'producto' => $producto->id,
        'id_presentacion' => $caja->id,
        'es_principal' => true,
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
            'presentaciones' => [
                ['id_presentacion' => $caja->id, 'unidades' => 10, 'precio' => 90, 'es_principal' => 1],
                ['id_presentacion' => $caja->id, 'unidades' => 5, 'precio' => 50],
            ],
        ]);

    $response->assertSessionHasErrors('presentaciones.1.id_presentacion');
    $this->assertDatabaseMissing('productos', ['codigo_barras' => '7501112223334']);
});

test('el store rechaza presentaciones inactivas', function () {
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Inactiva',
        'direccion' => 'Calle 2',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $categoria = Categoria::create(['nombre' => 'Respiratorios']);
    $caja = PresentacionProducto::create(['presentacion' => 'Caja', 'descripcion' => '', 'es_activo' => false]);

    $this->actingAs(crearUsuarioDePrueba($sucursal));

    $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->from(route('inventario.create'))
        ->post(route('inventario.store'), [
            'codigo_barras' => '7501112223399',
            'nombre_producto' => 'Salbutamol',
            'id_categoria' => $categoria->id,
            'presentaciones' => [
                ['id_presentacion' => $caja->id, 'unidades' => 10, 'precio' => 30],
            ],
        ])
        ->assertSessionHasErrors('presentaciones.0.id_presentacion');

    $this->assertDatabaseMissing('productos', ['codigo_barras' => '7501112223399']);
});

test('la presentacion unidad se guarda siempre con una unidad', function () {
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Test',
        'direccion' => 'Calle 1',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $categoria = Categoria::create(['nombre' => 'Respiratorios']);
    $unidad = presentacionUnidad();

    $this->actingAs(crearUsuarioDePrueba($sucursal));

    $response = $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->post(route('inventario.store'), [
            'codigo_barras' => '7505556667778',
            'nombre_producto' => 'Salbutamol',
            'id_categoria' => $categoria->id,
            'presentaciones' => [
                ['id_presentacion' => $unidad->id, 'unidades' => 5, 'precio' => 30, 'es_principal' => 1],
            ],
        ]);

    $response->assertRedirect(route('inventario.productos', ['sucursal' => $sucursal->id]));

    $producto = Producto::where('codigo_barras', '7505556667778')->firstOrFail();

    $this->assertDatabaseHas('presentacion_producto', [
        'producto' => $producto->id,
        'id_presentacion' => $unidad->id,
        'unidades' => 1,
        'es_principal' => true,
    ]);
});

test('no se generan productos con multiples presentaciones principales', function () {
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Unidades',
        'direccion' => 'Calle 9',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $categoria = Categoria::create(['nombre' => 'Analgésicos']);
    $caja = PresentacionProducto::create(['presentacion' => 'Caja', 'descripcion' => '']);
    $blister = PresentacionProducto::create(['presentacion' => 'Blister', 'descripcion' => '']);

    $this->actingAs(crearUsuarioDePrueba($sucursal));

    $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->post(route('inventario.store'), [
            'codigo_barras' => '7505556667785',
            'nombre_producto' => 'Ibuprofeno 400mg',
            'id_categoria' => $categoria->id,
            'presentaciones' => [
                ['id_presentacion' => $caja->id, 'unidades' => 10, 'precio' => 30, 'es_principal' => 1],
                ['id_presentacion' => $blister->id, 'unidades' => 5, 'precio' => 20, 'es_principal' => 1],
            ],
        ])
        ->assertRedirect(route('inventario.productos', ['sucursal' => $sucursal->id]));

    $producto = Producto::where('codigo_barras', '7505556667785')->firstOrFail();

    expect($producto->presentacionesPrecio()->where('es_principal', true)->count())->toBe(1);
});

test('el formulario bloquea la presentacion principal y no permite elegirla', function () {
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

    $response->assertOk()
        ->assertSee('bloquearPresentacion', false)
        ->assertSee('presentacionesQuitadas', false)
        ->assertDontSee('marcarPrincipal', false)
        ->assertDontSee('>Principal</label>', false);

    preg_match('/catalogoPresentaciones:\s*(\[[^\]]*\])/', $response->getContent(), $coincidencia);

    $catalogo = json_decode($coincidencia[1] ?? '[]', true);

    expect($catalogo)->not->toBeEmpty()
        ->and(collect($catalogo)->pluck('presentacion')->all())->toContain('Caja');
});

test('el formulario avisa si no hay presentaciones en el catalogo', function () {
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal SinCaja',
        'direccion' => 'Av. SinCaja 2',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    Categoria::create(['nombre' => 'Heridas']);

    $this->actingAs(crearUsuarioDePrueba($sucursal));

    $response = $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('inventario.create'));

    $response->assertOk()->assertSee('No hay presentaciones registradas');
});

test('el store ignora la presentacion principal enviada y mantiene caja', function () {
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
                ['id_presentacion' => $caja->id, 'unidades' => 14, 'precio' => 45, 'es_principal' => 0],
                ['id_presentacion' => $blister->id, 'unidades' => 10, 'precio' => 30, 'es_principal' => 1],
            ],
        ]);

    $response->assertRedirect(route('inventario.productos', ['sucursal' => $sucursal->id]));

    $producto = Producto::where('codigo_barras', '7504445556667')->firstOrFail();

    $this->assertDatabaseHas('presentacion_producto', [
        'producto' => $producto->id,
        'id_presentacion' => $caja->id,
        'es_principal' => true,
    ]);

    $this->assertDatabaseHas('presentacion_producto', [
        'producto' => $producto->id,
        'id_presentacion' => $blister->id,
        'es_principal' => false,
    ]);
});

test('la edicion conserva la presentacion principal aunque el formulario intente cambiarla', function () {
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal PrincipalFija',
        'direccion' => 'Av. PrincipalFija 1',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $categoria = Categoria::create(['nombre' => 'Gastro']);
    $caja = PresentacionProducto::create(['presentacion' => 'Caja', 'descripcion' => '']);
    $blister = PresentacionProducto::create(['presentacion' => 'Blister', 'descripcion' => '']);

    $producto = Producto::create([
        'codigo_barras' => '7504445556674',
        'nombre_producto' => 'Omeprazol 10mg',
        'descripcion' => '',
        'id_categoria' => $categoria->id,
        'es_controlado' => false,
        'es_activo' => true,
    ]);

    $producto->presentacionesPrecio()->create([
        'id_presentacion' => $caja->id,
        'unidades' => 14,
        'precio_presentacion' => 45,
        'es_principal' => false,
    ]);

    $producto->presentacionesPrecio()->create([
        'id_presentacion' => $blister->id,
        'unidades' => 10,
        'precio_presentacion' => 30,
        'es_principal' => true,
    ]);

    $this->actingAs(crearUsuarioDePrueba($sucursal));

    $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->put(route('inventario.update', $producto), [
            'codigo_barras' => $producto->codigo_barras,
            'nombre_producto' => $producto->nombre_producto,
            'id_categoria' => $categoria->id,
            'presentaciones' => [
                ['id_presentacion' => $caja->id, 'unidades' => 14, 'precio' => 45, 'es_principal' => 1],
                ['id_presentacion' => $blister->id, 'unidades' => 10, 'precio' => 30, 'es_principal' => 0],
            ],
        ])
        ->assertRedirect(route('inventario.productos', ['sucursal' => $sucursal->id]));

    $this->assertDatabaseHas('presentacion_producto', [
        'producto' => $producto->id,
        'id_presentacion' => $blister->id,
        'es_principal' => true,
    ]);

    $this->assertDatabaseHas('presentacion_producto', [
        'producto' => $producto->id,
        'id_presentacion' => $caja->id,
        'es_principal' => false,
    ]);
});

test('el store registra un producto solo por unidad con la presentacion unidad', function () {
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Bloqueo',
        'direccion' => 'Av. Bloqueo 4',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $categoria = Categoria::create(['nombre' => 'Respiratorio']);
    $unidad = presentacionUnidad();

    $this->actingAs(crearUsuarioDePrueba($sucursal));

    $response = $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->post(route('inventario.store'), [
            'codigo_barras' => '7507778889990',
            'nombre_producto' => 'Solución fisiológica',
            'id_categoria' => $categoria->id,
            'presentaciones' => [
                ['id_presentacion' => $unidad->id, 'unidades' => 1, 'precio' => 8.5, 'es_principal' => 1],
            ],
        ]);

    $response->assertRedirect(route('inventario.productos', ['sucursal' => $sucursal->id]));

    $producto = Producto::where('codigo_barras', '7507778889990')->firstOrFail();

    $this->assertDatabaseHas('presentacion_producto', [
        'producto' => $producto->id,
        'id_presentacion' => $unidad->id,
        'unidades' => 1,
        'precio_presentacion' => 8.5,
        'es_principal' => true,
    ]);
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

test('el store rechaza presentaciones con cero unidades', function () {
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Unitaria',
        'direccion' => 'Av. Unitaria 7',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $categoria = Categoria::create(['nombre' => 'Inyectables']);
    $caja = PresentacionProducto::create(['presentacion' => 'Caja', 'descripcion' => '']);

    $this->actingAs(crearUsuarioDePrueba($sucursal));

    $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->from(route('inventario.create'))
        ->post(route('inventario.store'), [
            'codigo_barras' => '7503334445556',
            'nombre_producto' => 'Solución fisiológica',
            'id_categoria' => $categoria->id,
            'presentaciones' => [
                ['id_presentacion' => $caja->id, 'unidades' => 0, 'precio' => 8.5],
            ],
        ])
        ->assertSessionHasErrors('presentaciones.0.unidades');

    $this->assertDatabaseMissing('productos', ['codigo_barras' => '7503334445556']);
});

test('el store rechaza un producto sin presentaciones ni venta por unidad', function () {
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Invendible',
        'direccion' => 'Av. Invendible 8',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $categoria = Categoria::create(['nombre' => 'Dermatológicos']);
    PresentacionProducto::create(['presentacion' => 'Caja', 'descripcion' => '']);

    $this->actingAs(crearUsuarioDePrueba($sucursal));

    $response = $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->from(route('inventario.create'))
        ->post(route('inventario.store'), [
            'codigo_barras' => '7503334447778',
            'nombre_producto' => 'Producto invendible',
            'id_categoria' => $categoria->id,
        ]);

    $response->assertSessionHasErrors('presentaciones');
    $this->assertDatabaseMissing('productos', ['codigo_barras' => '7503334447778']);
});

test('la edicion puede dejar solo la presentacion unidad como principal', function () {
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal QuitarFracciones',
        'direccion' => 'Av. Quitar 9',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $categoria = Categoria::create(['nombre' => 'Soluciones']);
    $caja = PresentacionProducto::create(['presentacion' => 'Caja', 'descripcion' => '']);
    $unidad = presentacionUnidad();

    $producto = Producto::create([
        'codigo_barras' => '7503334449990',
        'nombre_producto' => 'Amoxicilina suspensión',
        'descripcion' => '',
        'id_categoria' => $categoria->id,
        'es_controlado' => false,
        'es_activo' => true,
    ]);

    $producto->presentacionesPrecio()->create([
        'id_presentacion' => $caja->id,
        'unidades' => 10,
        'precio_presentacion' => 90,
        'es_principal' => true,
    ]);

    $this->actingAs(crearUsuarioDePrueba($sucursal));

    $response = $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->put(route('inventario.update', $producto), [
            'codigo_barras' => $producto->codigo_barras,
            'nombre_producto' => $producto->nombre_producto,
            'id_categoria' => $categoria->id,
            'presentaciones' => [
                ['id_presentacion' => $unidad->id, 'unidades' => 1, 'precio' => 9, 'es_principal' => 1],
            ],
        ]);

    $response->assertRedirect(route('inventario.productos', ['sucursal' => $sucursal->id]));

    $this->assertDatabaseMissing('presentacion_producto', [
        'producto' => $producto->id,
        'id_presentacion' => $caja->id,
    ]);

    $this->assertDatabaseHas('presentacion_producto', [
        'producto' => $producto->id,
        'id_presentacion' => $unidad->id,
        'unidades' => 1,
        'es_principal' => true,
    ]);
});

test('el formulario de crear muestra la presentacion principal bloqueada', function () {
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal ModalUnitaria',
        'direccion' => 'Av. Modal 10',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    Categoria::create(['nombre' => 'Vitaminas']);
    PresentacionProducto::create(['presentacion' => 'Caja', 'descripcion' => '']);

    $this->actingAs(crearUsuarioDePrueba($sucursal));

    $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('inventario.create'))
        ->assertOk()
        ->assertSee('bloquearPresentacion', false)
        ->assertSee('principalIndex', false)
        ->assertDontSee('marcarPrincipal', false)
        ->assertDontSee('confirmarUnitario', false);
});

test('el formulario de edicion precarga las presentaciones del producto', function () {
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal EdicionUnitaria',
        'direccion' => 'Av. Edición 11',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $categoria = Categoria::create(['nombre' => 'Oftálmicos']);
    $caja = PresentacionProducto::create(['presentacion' => 'Caja', 'descripcion' => '']);

    $producto = Producto::create([
        'codigo_barras' => '7503334441112',
        'nombre_producto' => 'Lágrimas artificiales',
        'descripcion' => '',
        'id_categoria' => $categoria->id,
        'es_controlado' => false,
        'es_activo' => true,
    ]);

    $producto->presentacionesPrecio()->create([
        'id_presentacion' => $caja->id,
        'unidades' => 10,
        'precio_presentacion' => 12,
        'stock_minimo' => 7,
        'es_principal' => true,
    ]);

    $this->actingAs(crearUsuarioDePrueba($sucursal));

    $this->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('inventario.edit', $producto))
        ->assertOk()
        ->assertSee('principalIndex: 0', false)
        ->assertSee('"unidades":10', false)
        ->assertSee('"stock_minimo":7', false);
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
