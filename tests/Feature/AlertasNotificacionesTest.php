<?php

use App\Models\Modulo;
use App\Models\PermisoActivado;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\Traspaso;
use App\Models\User;
use App\Support\AlertasResumen;

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
