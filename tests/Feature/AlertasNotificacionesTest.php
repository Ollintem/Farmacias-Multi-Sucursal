<?php

use App\Models\Alerta;
use App\Models\AlertaUsuario;
use App\Models\Categoria;
use App\Models\Inventario;
use App\Models\Lote;
use App\Models\Modulo;
use App\Models\PermisoActivado;
use App\Models\PresentacionProducto;
use App\Models\Producto;
use App\Models\ProductoPresentacion;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\Traspaso;
use App\Models\User;
use App\Support\AlertasFeed;
use App\Support\AlertasResumen;
use Illuminate\Support\Carbon;

function crearSucursalAlertas(string $nombre): Sucursal
{
    return Sucursal::create([
        'nombre_sucursal' => $nombre,
        'direccion' => 'Calle 1',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);
}

function usuarioConAlertas(Sucursal $sucursal): User
{
    $rol = Rol::firstOrCreate(['tipo_rol' => 'SuperAdmin'], ['descripcion' => 'Acceso total']);
    $usuario = User::factory()->create(['id_rol' => $rol->id, 'id_sucursal' => $sucursal->id]);

    foreach (['Dashboard', 'Alertas'] as $nombre) {
        $modulo = Modulo::firstOrCreate(['nombre_modulo' => $nombre]);
        PermisoActivado::create(['id_modulo' => $modulo->id, 'id_usuario' => $usuario->id, 'puede_ver' => true]);
    }

    return $usuario;
}

it('cuenta traspasos pendientes y enviados como notificaciones', function () {
    $origen = crearSucursalAlertas('Origen');
    $destino = crearSucursalAlertas('Destino');
    $rol = Rol::firstOrCreate(['tipo_rol' => 'SuperAdmin'], ['descripcion' => 'Acceso total']);
    $usuario = User::factory()->create(['id_rol' => $rol->id, 'id_sucursal' => $destino->id]);

    Traspaso::create(['sucursal_a' => $origen->id, 'sucursal_b' => $destino->id, 'pedido_por' => $usuario->id, 'estado' => 'pendiente']);
    Traspaso::create(['sucursal_a' => $origen->id, 'sucursal_b' => $destino->id, 'pedido_por' => $usuario->id, 'estado' => 'enviado']);

    $conteo = AlertasResumen::counts($destino->id);

    expect($conteo['pendientes'])->toBe(2)
        ->and($conteo['total'])->toBe(2);
});

it('muestra el numero de notificaciones en el menu Alertas', function () {
    $origen = crearSucursalAlertas('Origen Menu');
    $destino = crearSucursalAlertas('Destino Menu');
    $usuario = usuarioConAlertas($destino);

    Traspaso::create(['sucursal_a' => $origen->id, 'sucursal_b' => $destino->id, 'pedido_por' => $usuario->id, 'estado' => 'pendiente']);

    $contenido = $this->actingAs($usuario)
        ->withSession(['active_sucursal_id' => $destino->id])
        ->get(route('dashboard'))
        ->assertOk()
        ->getContent();

    expect($contenido)->toContain('data-flux-navlist-badge>1<');
});

it('oculta el numero cuando no hay notificaciones', function () {
    $sucursal = crearSucursalAlertas('Sin Nada');
    $usuario = usuarioConAlertas($sucursal);

    $contenido = $this->actingAs($usuario)
        ->withSession(['active_sucursal_id' => $sucursal->id])
        ->get(route('dashboard'))
        ->assertOk()
        ->getContent();

    expect($contenido)->not->toContain('data-flux-navlist-badge>');
});

it('marca una sola notificacion como leida desde su menu', function () {
    $origen = crearSucursalAlertas('Origen Leer');
    $destino = crearSucursalAlertas('Destino Leer');
    $usuario = usuarioConAlertas($destino);

    $traspaso = Traspaso::create(['sucursal_a' => $origen->id, 'sucursal_b' => $destino->id, 'pedido_por' => $usuario->id, 'estado' => 'pendiente']);

    $this->actingAs($usuario)
        ->withSession(['active_sucursal_id' => $destino->id])
        ->post(route('alertas.leer'), ['id' => "traspaso:{$traspaso->id}"])
        ->assertRedirect();

    expect(AlertasFeed::leidas($destino->id, $usuario->id))->toContain("traspaso:{$traspaso->id}");
});

it('persiste lo leido en base de datos por usuario y sucursal', function () {
    $origen = crearSucursalAlertas('Origen BD');
    $destino = crearSucursalAlertas('Destino BD');
    $usuario = usuarioConAlertas($destino);

    $traspaso = Traspaso::create(['sucursal_a' => $origen->id, 'sucursal_b' => $destino->id, 'pedido_por' => $usuario->id, 'estado' => 'pendiente']);

    expect(AlertasFeed::noLeidasCount($destino->id, $usuario->id))->toBe(1);

    AlertasFeed::marcarUna($destino->id, "traspaso:{$traspaso->id}", $usuario->id);

    expect(AlertasFeed::noLeidasCount($destino->id, $usuario->id))->toBe(0)
        ->and(AlertaUsuario::where('id_usuario', $usuario->id)->where('estado', AlertaUsuario::ESTADO_LEIDA)->whereNotNull('fecha_leido')->exists())->toBeTrue();
});

it('formatea el tiempo corto estilo red social', function () {
    expect(AlertasFeed::tiempoCorto(null))->toBe('reciente')
        ->and(AlertasFeed::tiempoCorto(now()->subMinutes(5)))->toBe('5 min')
        ->and(AlertasFeed::tiempoCorto(now()->subHours(4)))->toBe('4 h')
        ->and(AlertasFeed::tiempoCorto(now()->subDays(3)))->toBe('3 d');
});

it('formatea fechas futuras como "en ..." y fechas lejanas con mes en espanol', function () {
    expect(AlertasFeed::tiempoCorto(now()->addMinutes(5)))->toBe('en 5 min')
        ->and(AlertasFeed::tiempoCorto(now()->addHours(4)))->toBe('en 4 h')
        ->and(AlertasFeed::tiempoCorto(now()->addDays(3)))->toBe('en 3 d')
        ->and(AlertasFeed::tiempoCorto(now()->addDays(10)))->not->toBe('ahora mismo')
        ->and(AlertasFeed::tiempoCorto(Carbon::parse('2020-01-15 12:00:00')))->toBe('15 ene. 2020');
});

it('genera alertas de stock bajo cuando el stock llega al minimo configurado', function () {
    $sucursal = crearSucursalAlertas('Stock Minimo');
    $usuario = usuarioConAlertas($sucursal);

    $categoria = Categoria::create(['nombre' => 'Analgésicos Stock']);
    $caja = PresentacionProducto::create(['presentacion' => 'Caja', 'descripcion' => '']);
    $producto = Producto::create([
        'codigo_barras' => '7501234567890',
        'nombre_producto' => 'Paracetamol Stock',
        'descripcion' => '',
        'stock' => 0,
        'precio' => null,
        'id_presentacion' => $caja->id,
        'id_categoria' => $categoria->id,
        'es_controlado' => false,
        'es_activo' => true,
    ]);

    ProductoPresentacion::create([
        'producto' => $producto->id,
        'id_presentacion' => $caja->id,
        'unidades' => 10,
        'precio_presentacion' => 50,
        'stock_minimo' => 10,
        'es_principal' => true,
    ]);

    $lote = Lote::create([
        'folio' => 'L-STOCK-ALERTA',
        'stock_lote' => 8,
        'id_producto' => $producto->id,
        'id_presentacion' => $caja->id,
        'fecha_caducidad' => '2027-12-31',
    ]);

    Inventario::create([
        'id_sucursal' => $sucursal->id,
        'id_lote' => $lote->id,
        'stock_individual' => 8,
    ]);

    expect(collect(AlertasFeed::items($sucursal->id))->pluck('id'))
        ->toContain("stock:{$producto->id}");

    AlertasFeed::marcarUna($sucursal->id, "stock:{$producto->id}", $usuario->id);

    expect(Alerta::where('entidad_tipo', 'producto')->where('entidad_id', $producto->id)->exists())->toBeTrue();

    Inventario::query()->where('id_lote', $lote->id)->update(['stock_individual' => 20]);

    expect(collect(AlertasFeed::listado($sucursal->id, $usuario->id))->pluck('id'))
        ->not->toContain("stock:{$producto->id}");

    expect(Alerta::where('entidad_tipo', 'producto')
        ->where('entidad_id', $producto->id)
        ->where('estado', Alerta::ESTADO_RESUELTA)
        ->exists())->toBeTrue();
});

it('muestra el listado estilo facebook con avatar, tiempo y menu', function () {
    $origen = crearSucursalAlertas('Origen FB');
    $destino = crearSucursalAlertas('Destino FB');
    $usuario = usuarioConAlertas($destino);

    Traspaso::create(['sucursal_a' => $origen->id, 'sucursal_b' => $destino->id, 'pedido_por' => $usuario->id, 'estado' => 'pendiente']);

    $contenido = $this->actingAs($usuario)
        ->withSession(['active_sucursal_id' => $destino->id])
        ->get(route('alertas.index'))
        ->assertOk()
        ->getContent();

    expect($contenido)
        ->toContain('noti-avatar')
        ->toContain('noti-insignia')
        ->toContain('•••')
        ->toContain('alertas/leer');
});

it('ofrece ver detalle e ir al apartado desde el listado', function () {
    $origen = crearSucursalAlertas('Origen Botones');
    $destino = crearSucursalAlertas('Destino Botones');
    $usuario = usuarioConAlertas($destino);

    $traspaso = Traspaso::create(['sucursal_a' => $origen->id, 'sucursal_b' => $destino->id, 'pedido_por' => $usuario->id, 'estado' => 'pendiente']);

    $contenido = $this->actingAs($usuario)
        ->withSession(['active_sucursal_id' => $destino->id])
        ->get(route('alertas.index'))
        ->assertOk()
        ->getContent();

    expect($contenido)
        ->toContain('data-noti-detalle="traspaso:'.$traspaso->id.'"')
        ->toContain('data-noti-ir="traspaso:'.$traspaso->id.'"');
});

it('devuelve el breve detalle con boton al apartado segun el tipo', function () {
    $origen = crearSucursalAlertas('Origen Detalle');
    $destino = crearSucursalAlertas('Destino Detalle');
    $usuario = usuarioConAlertas($destino);

    $traspaso = Traspaso::create(['sucursal_a' => $origen->id, 'sucursal_b' => $destino->id, 'pedido_por' => $usuario->id, 'estado' => 'pendiente']);

    $respuesta = $this->actingAs($usuario)
        ->withSession(['active_sucursal_id' => $destino->id])
        ->getJson(route('alertas.detalle', ['id' => "traspaso:{$traspaso->id}"]))
        ->assertOk()
        ->json('aviso');

    expect($respuesta['id'])->toBe("traspaso:{$traspaso->id}")
        ->and($respuesta['titulo'])->toContain("T-{$traspaso->id}")
        ->and($respuesta['destino_url'])->toContain('entradas-de-almacen')
        ->and($respuesta['destino_etiqueta'])->toBe('Ir a traspasos');
});

it('permite desmarcar una notificacion para regresarla a no leida', function () {
    $origen = crearSucursalAlertas('Origen Desmarcar');
    $destino = crearSucursalAlertas('Destino Desmarcar');
    $usuario = usuarioConAlertas($destino);

    $traspaso = Traspaso::create(['sucursal_a' => $origen->id, 'sucursal_b' => $destino->id, 'pedido_por' => $usuario->id, 'estado' => 'pendiente']);

    AlertasFeed::marcarUna($destino->id, "traspaso:{$traspaso->id}", $usuario->id);
    expect(AlertasFeed::noLeidasCount($destino->id, $usuario->id))->toBe(0);

    $this->actingAs($usuario)
        ->withSession(['active_sucursal_id' => $destino->id])
        ->postJson(route('alertas.no-leida'), ['id' => "traspaso:{$traspaso->id}"])
        ->assertOk()
        ->assertJson(['leida' => false]);

    expect(AlertasFeed::noLeidasCount($destino->id, $usuario->id))->toBe(1);
});
