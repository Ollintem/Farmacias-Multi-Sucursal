<?php

use App\Models\Alerta;
use App\Models\AlertaUsuario;
use App\Models\Lote;
use App\Models\Modulo;
use App\Models\PermisoActivado;
use App\Models\PresentacionProducto;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\TipoAlerta;
use App\Models\Traspaso;
use App\Models\User;

function crearSucursalClasificacion(string $nombre): Sucursal
{
    return Sucursal::create([
        'nombre_sucursal' => $nombre,
        'direccion' => 'Calle 1',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);
}

function usuarioConAlertasClasificacion(Sucursal $sucursal): User
{
    $rol = Rol::firstOrCreate(['tipo_rol' => 'SuperAdmin'], ['descripcion' => 'Acceso total']);
    $usuario = User::factory()->create(['id_rol' => $rol->id, 'id_sucursal' => $sucursal->id]);

    foreach (['Dashboard', 'Alertas'] as $nombre) {
        $modulo = Modulo::firstOrCreate(['nombre_modulo' => $nombre]);
        PermisoActivado::create(['id_modulo' => $modulo->id, 'id_usuario' => $usuario->id, 'puede_ver' => true]);
    }

    return $usuario;
}

function tipoAlertaClasificacion(string $nombre, string $modulo, string $nivel): TipoAlerta
{
    $moduloModelo = Modulo::firstOrCreate(['nombre_modulo' => $modulo]);

    return TipoAlerta::updateOrCreate(
        ['nombre' => $nombre],
        ['id_modulo' => $moduloModelo->id, 'nivel' => $nivel, 'activo' => true]
    );
}

function productoClasificacion(string $codigo, string $nombre): Producto
{
    $presentacion = PresentacionProducto::firstOrCreate(
        ['presentacion' => 'Caja'],
        ['descripcion' => '']
    );

    return Producto::create([
        'codigo_barras' => $codigo,
        'nombre_producto' => $nombre,
        'descripcion' => '',
        'precio' => 10.0,
        'id_presentacion' => $presentacion->id,
        'es_controlado' => false,
        'es_activo' => true,
    ]);
}

it('clasifica las alertas en stock, caducidad y traspasos', function () {
    $origen = crearSucursalClasificacion('Origen Clasif');
    $destino = crearSucursalClasificacion('Destino Clasif');
    $usuario = usuarioConAlertasClasificacion($destino);

    $producto = productoClasificacion('7500000000101', 'Producto Stock Bajo');

    $tipoStock = tipoAlertaClasificacion('STOCK_BAJO', 'Inventario', TipoAlerta::NIVEL_ADVERTENCIA);
    $tipoCaducidad = tipoAlertaClasificacion('CADUCIDAD_PROXIMA', 'Lotes y caducidades', TipoAlerta::NIVEL_ADVERTENCIA);
    $tipoPendiente = tipoAlertaClasificacion('TRASPASO_PENDIENTE', 'Traspasos', TipoAlerta::NIVEL_ADVERTENCIA);
    $tipoRecibido = tipoAlertaClasificacion('TRASPASO_RECIBIDO', 'Traspasos', TipoAlerta::NIVEL_INFO);
    $tipoRechazado = tipoAlertaClasificacion('TRASPASO_RECHAZADO', 'Traspasos', TipoAlerta::NIVEL_ADVERTENCIA);

    Alerta::create([
        'id_tipo_alerta' => $tipoStock->id,
        'id_sucursal' => $destino->id,
        'entidad_tipo' => 'producto',
        'entidad_id' => $producto->id,
        'estado' => Alerta::ESTADO_ACTIVA,
    ]);

    $lote = Lote::create([
        'folio' => 'L-CLASIF-CAD',
        'stock_lote' => 5,
        'id_producto' => $producto->id,
        'fecha_caducidad' => now()->addDays(10)->format('Y-m-d'),
    ]);

    Alerta::create([
        'id_tipo_alerta' => $tipoCaducidad->id,
        'id_sucursal' => $destino->id,
        'entidad_tipo' => 'lote',
        'entidad_id' => $lote->id,
        'estado' => Alerta::ESTADO_ACTIVA,
    ]);

    Traspaso::create(['sucursal_a' => $origen->id, 'sucursal_b' => $destino->id, 'pedido_por' => $usuario->id, 'estado' => 'pendiente']);
    $recibido = Traspaso::create(['sucursal_a' => $origen->id, 'sucursal_b' => $destino->id, 'pedido_por' => $usuario->id, 'estado' => 'aceptado']);
    $rechazado = Traspaso::create(['sucursal_a' => $origen->id, 'sucursal_b' => $destino->id, 'pedido_por' => $usuario->id, 'estado' => 'rechazado']);

    Alerta::create([
        'id_tipo_alerta' => $tipoRecibido->id,
        'id_sucursal' => $destino->id,
        'entidad_tipo' => 'traspaso',
        'entidad_id' => $recibido->id,
        'estado' => Alerta::ESTADO_ACTIVA,
    ]);
    Alerta::create([
        'id_tipo_alerta' => $tipoRechazado->id,
        'id_sucursal' => $destino->id,
        'entidad_tipo' => 'traspaso',
        'entidad_id' => $rechazado->id,
        'estado' => Alerta::ESTADO_ACTIVA,
    ]);

    $pagina = fn (string $filtro) => $this->actingAs($usuario)
        ->withSession(['active_sucursal_id' => $destino->id])
        ->get(route('alertas.index', ['sucursal' => $destino->id, 'filtro' => $filtro]))
        ->assertOk()
        ->getContent();

    expect($pagina('stock'))->toContain('Stock bajo: Producto Stock Bajo')
        ->and($pagina('caducidad'))->toContain('Por caducar')
        ->and($pagina('traspasos'))->toContain('recibido')
        ->and($pagina('traspasos'))->toContain('rechazado')
        ->and($pagina('stock'))->not->toContain('Por caducar')
        ->and($pagina('caducidad'))->not->toContain('Stock bajo');
});

it('filtra no leidas y marca leida por id de alerta', function () {
    $origen = crearSucursalClasificacion('Origen Lectura');
    $destino = crearSucursalClasificacion('Destino Lectura');
    $usuario = usuarioConAlertasClasificacion($destino);

    $traspaso = Traspaso::create(['sucursal_a' => $origen->id, 'sucursal_b' => $destino->id, 'pedido_por' => $usuario->id, 'estado' => 'pendiente']);

    $this->actingAs($usuario)
        ->withSession(['active_sucursal_id' => $destino->id])
        ->get(route('alertas.index', ['sucursal' => $destino->id]))
        ->assertOk();

    $alertaId = Alerta::query()
        ->where('id_sucursal', $destino->id)
        ->where('entidad_tipo', 'traspaso')
        ->where('entidad_id', $traspaso->id)
        ->value('id');

    expect($alertaId)->not->toBeNull();

    $noLeidas = fn () => $this->actingAs($usuario)
        ->withSession(['active_sucursal_id' => $destino->id])
        ->get(route('alertas.index', ['sucursal' => $destino->id, 'filtro' => 'no_leidas']))
        ->assertOk()
        ->getContent();

    expect($noLeidas())->toContain("Traspaso T-{$traspaso->id}");

    $this->actingAs($usuario)
        ->withSession(['active_sucursal_id' => $destino->id])
        ->post(route('alertas.leer'), ['id' => (string) $alertaId])
        ->assertRedirect();

    expect(AlertaUsuario::where('id_alerta', $alertaId)->where('id_usuario', $usuario->id)->where('estado', AlertaUsuario::ESTADO_LEIDA)->whereNotNull('fecha_leido')->exists())->toBeTrue()
        ->and($noLeidas())->not->toContain("Traspaso T-{$traspaso->id}");
});
