<?php

use App\Models\Modulo;
use App\Models\PermisoActivado;
use App\Models\Rol;
use App\Models\RolPermiso;
use App\Models\User;
use Database\Seeders\ModuloSeeder;
use Database\Seeders\RolesPermisosSeeder;
use Database\Seeders\RolSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

function crearRolParaPermisos(string $tipoRol): Rol
{
    return Rol::create([
        'tipo_rol' => $tipoRol,
        'descripcion' => 'Rol de prueba',
    ]);
}

function crearUsuarioParaPermisos(Rol $rol): User
{
    return User::create([
        'nombre' => 'Usuario',
        'apellido' => 'Permisos',
        'email' => 'permisos_'.Str::random(10).'@example.test',
        'password' => Hash::make('password123'),
        'es_activo' => true,
        'id_rol' => $rol->id,
    ]);
}

/**
 * @param  array<string, bool>  $flags
 */
function asignarPermisoRol(Rol $rol, Modulo $modulo, array $flags): RolPermiso
{
    return RolPermiso::updateOrCreate(
        [
            'id_rol' => $rol->id,
            'id_modulo' => $modulo->id,
        ],
        [
            'puede_ver' => $flags['ver'] ?? false,
            'puede_crear' => $flags['crear'] ?? false,
            'puede_editar' => $flags['editar'] ?? false,
            'puede_borrar' => $flags['borrar'] ?? false,
        ]
    );
}

it('muestra la configuracion de permisos del rol seleccionado', function () {
    $this->seed([
        RolSeeder::class,
        ModuloSeeder::class,
        RolesPermisosSeeder::class,
    ]);

    $superAdmin = Rol::where('tipo_rol', 'SuperAdmin')->firstOrFail();
    $gerente = Rol::where('tipo_rol', 'Gerente')->firstOrFail();
    $usuario = crearUsuarioParaPermisos($superAdmin);

    $this->actingAs($usuario)
        ->get(route('roles.permisos', ['rol' => $gerente->id]))
        ->assertOk()
        ->assertSee('Permisos por rol')
        ->assertSee('Gerente')
        ->assertSee('Dashboard')
        ->assertSee('Corte ciego')
        ->assertSee('Guardar permisos');
});

it('actualiza solo el rol seleccionado sin afectar otros roles', function () {
    $rolAdmin = crearRolParaPermisos('SuperAdmin');
    $gerente = crearRolParaPermisos('Gerente');
    $cajero = crearRolParaPermisos('Cajero');
    $admin = crearUsuarioParaPermisos($rolAdmin);

    $modulo = Modulo::create(['nombre_modulo' => 'Inventario']);

    asignarPermisoRol($cajero, $modulo, ['ver' => true]);

    $this->actingAs($admin)
        ->put(route('roles.permisos.update', $gerente), [
            'permisos' => [$modulo->id => ['ver' => '1']],
        ])
        ->assertRedirect(route('roles.permisos', ['rol' => $gerente->id]))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('roles_permisos', [
        'id_rol' => $gerente->id,
        'id_modulo' => $modulo->id,
        'puede_ver' => true,
        'puede_crear' => false,
        'puede_editar' => false,
        'puede_borrar' => false,
    ]);

    $this->assertDatabaseHas('roles_permisos', [
        'id_rol' => $cajero->id,
        'id_modulo' => $modulo->id,
        'puede_ver' => true,
    ]);

    $this->assertDatabaseCount('roles_permisos', 2);
});

it('impide modificar permisos sin autorizacion de edicion', function () {
    $rolCajero = crearRolParaPermisos('Cajero');
    $rolGerente = crearRolParaPermisos('Gerente');
    $usuario = crearUsuarioParaPermisos($rolCajero);

    $moduloUsuarios = Modulo::create(['nombre_modulo' => 'Usuarios y roles']);
    $moduloInventario = Modulo::create(['nombre_modulo' => 'Inventario']);

    asignarPermisoRol($rolCajero, $moduloUsuarios, ['ver' => true, 'editar' => false]);

    $this->actingAs($usuario)
        ->put(route('roles.permisos.update', $rolGerente), [
            'permisos' => [$moduloInventario->id => ['ver' => '1']],
        ])
        ->assertForbidden();

    $this->assertDatabaseMissing('roles_permisos', [
        'id_rol' => $rolGerente->id,
        'id_modulo' => $moduloInventario->id,
    ]);
});

it('impide restringir el rol SuperAdmin', function () {
    $superAdmin = crearRolParaPermisos('SuperAdmin');
    $usuario = crearUsuarioParaPermisos($superAdmin);
    $modulo = Modulo::create(['nombre_modulo' => 'Inventario']);

    $this->actingAs($usuario)
        ->put(route('roles.permisos.update', $superAdmin), [
            'permisos' => [$modulo->id => ['ver' => '1']],
        ])
        ->assertSessionHas('error');

    $this->assertDatabaseMissing('roles_permisos', [
        'id_rol' => $superAdmin->id,
        'id_modulo' => $modulo->id,
    ]);
});

it('muestra la configuracion en solo lectura cuando no puede editar', function () {
    $rol = crearRolParaPermisos('Cajero');
    $usuario = crearUsuarioParaPermisos($rol);
    $modulo = Modulo::create(['nombre_modulo' => 'Usuarios y roles']);

    asignarPermisoRol($rol, $modulo, ['ver' => true, 'editar' => false]);

    $this->actingAs($usuario)
        ->get(route('roles.permisos'))
        ->assertOk()
        ->assertSee('Solo lectura');
});

it('el permiso personalizado del usuario tiene prioridad sobre el rol', function () {
    $rol = crearRolParaPermisos('Cajero');
    $usuario = crearUsuarioParaPermisos($rol);
    $modulo = Modulo::create(['nombre_modulo' => 'Inventario']);

    asignarPermisoRol($rol, $modulo, ['ver' => false]);

    PermisoActivado::create([
        'id_usuario' => $usuario->id,
        'id_modulo' => $modulo->id,
        'puede_ver' => true,
        'puede_crear' => false,
        'puede_editar' => false,
        'puede_borrar' => false,
    ]);

    expect($usuario->permisosHabilitados('ver', 'inventario'))->toBeTrue()
        ->and($usuario->puedeVerModulo('Inventario'))->toBeTrue();
});

it('el rol autoriza sin necesidad de una excepcion individual', function () {
    $rol = crearRolParaPermisos('Cajero');
    $usuario = crearUsuarioParaPermisos($rol);
    $modulo = Modulo::create(['nombre_modulo' => 'Dashboard']);

    asignarPermisoRol($rol, $modulo, ['ver' => true]);

    expect($usuario->permisosHabilitados('ver', 'dashboard'))->toBeTrue()
        ->and($usuario->puedeVerModulo('Dashboard'))->toBeTrue();

    $this->actingAs($usuario)
        ->get(route('dashboard'))
        ->assertOk();
});

it('una excepcion individual puede restringir un permiso concedido por el rol', function () {
    $rol = crearRolParaPermisos('Cajero');
    $usuario = crearUsuarioParaPermisos($rol);
    $modulo = Modulo::create(['nombre_modulo' => 'Inventario']);

    asignarPermisoRol($rol, $modulo, ['ver' => true]);

    PermisoActivado::create([
        'id_usuario' => $usuario->id,
        'id_modulo' => $modulo->id,
        'puede_ver' => false,
        'puede_crear' => false,
        'puede_editar' => false,
        'puede_borrar' => false,
    ]);

    expect($usuario->permisosHabilitados('ver', 'inventario'))->toBeFalse()
        ->and($usuario->puedeVerModulo('Inventario'))->toBeFalse();
});

it('modificar permisos de un usuario no altera los del rol ni los de otros usuarios', function () {
    $rol = crearRolParaPermisos('Cajero');
    $usuario = crearUsuarioParaPermisos($rol);
    $otroUsuario = crearUsuarioParaPermisos($rol);
    $modulo = Modulo::create(['nombre_modulo' => 'Inventario']);

    asignarPermisoRol($rol, $modulo, ['ver' => true]);

    PermisoActivado::create([
        'id_usuario' => $usuario->id,
        'id_modulo' => $modulo->id,
        'puede_ver' => false,
        'puede_crear' => false,
        'puede_editar' => false,
        'puede_borrar' => false,
    ]);

    expect($usuario->permisosHabilitados('ver', 'inventario'))->toBeFalse()
        ->and($otroUsuario->permisosHabilitados('ver', 'inventario'))->toBeTrue()
        ->and(RolPermiso::where('id_rol', $rol->id)->where('id_modulo', $modulo->id)->value('puede_ver'))->toBeTrue();
});

it('marca solo las excepciones individuales del usuario seleccionado', function () {
    $rolSuperAdmin = crearRolParaPermisos('SuperAdmin');
    $rolCajero = crearRolParaPermisos('Cajero');
    $admin = crearUsuarioParaPermisos($rolSuperAdmin);
    $usuario = crearUsuarioParaPermisos($rolCajero);

    $moduloConExcepcion = Modulo::create(['nombre_modulo' => 'Inventario']);
    $moduloSinExcepcion = Modulo::create(['nombre_modulo' => 'Reportes']);

    PermisoActivado::create([
        'id_usuario' => $usuario->id,
        'id_modulo' => $moduloConExcepcion->id,
        'puede_ver' => true,
        'puede_crear' => true,
        'puede_editar' => false,
        'puede_borrar' => false,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('roles.permisos', [
            'rol' => $rolCajero->id,
            'usuario' => $usuario->id,
        ]))
        ->assertOk()
        ->assertSee('Excepciones de '.$usuario->nombre)
        ->assertSee('data-excepcion-usuario="'.$usuario->id.'"', false)
        ->assertSee('data-excepcion-modulo="'.$moduloConExcepcion->id.'"', false)
        ->assertDontSee('data-excepcion-modulo="'.$moduloSinExcepcion->id.'"', false);

    expect($response->getContent())
        ->toMatch('/data-excepcion-modulo="'.$moduloConExcepcion->id.'"[\s\S]*?data-excepcion-checkbox[\s\S]*?checked/');
});

it('el seeder asigna permisos predeterminados y no sobrescribe cambios manuales', function () {
    $this->seed([
        RolSeeder::class,
        ModuloSeeder::class,
        RolesPermisosSeeder::class,
    ]);

    $totalModulos = Modulo::count();
    $superAdmin = Rol::where('tipo_rol', 'SuperAdmin')->firstOrFail();
    $gerente = Rol::where('tipo_rol', 'Gerente')->firstOrFail();
    $cajero = Rol::where('tipo_rol', 'Cajero')->firstOrFail();

    expect($totalModulos)->toBeGreaterThan(0)
        ->and(RolPermiso::where('id_rol', $superAdmin->id)->count())->toBe($totalModulos)
        ->and(RolPermiso::where('id_rol', $superAdmin->id)->where('puede_ver', true)->count())->toBe($totalModulos)
        ->and(RolPermiso::where('id_rol', $gerente->id)->count())->toBe($totalModulos)
        ->and(RolPermiso::where('id_rol', $cajero->id)->count())->toBe($totalModulos);

    $modulo = fn (string $nombre): Modulo => Modulo::where('nombre_modulo', $nombre)->firstOrFail();
    $permisoRol = fn (Rol $rol, Modulo $modulo): ?RolPermiso => RolPermiso::where('id_rol', $rol->id)
        ->where('id_modulo', $modulo->id)
        ->first();

    expect($permisoRol($gerente, $modulo('Caja'))?->puede_ver)->toBeTrue()
        ->and($permisoRol($gerente, $modulo('Sucursales'))?->puede_ver)->toBeFalse()
        ->and($permisoRol($gerente, $modulo('Usuarios y roles'))?->puede_ver)->toBeFalse()
        ->and($permisoRol($gerente, $modulo('Usuarios y roles'))?->puede_borrar)->toBeFalse();

    expect($permisoRol($cajero, $modulo('Punto de venta'))?->puede_ver)->toBeTrue()
        ->and($permisoRol($cajero, $modulo('Punto de venta'))?->puede_crear)->toBeTrue()
        ->and($permisoRol($cajero, $modulo('Inventario'))?->puede_ver)->toBeTrue()
        ->and($permisoRol($cajero, $modulo('Caja'))?->puede_ver)->toBeTrue()
        ->and($permisoRol($cajero, $modulo('Dashboard'))?->puede_ver)->toBeFalse()
        ->and($permisoRol($cajero, $modulo('Usuarios y roles'))?->puede_ver)->toBeFalse();

    expect($gerente->fresh()->corte_ciego)->toBeFalse()
        ->and($cajero->fresh()->corte_ciego)->toBeTrue()
        ->and($superAdmin->fresh()->corte_ciego)->toBeTrue();

    $permisoEditado = $permisoRol($gerente, $modulo('Inventario'));
    $permisoEditado->update(['puede_ver' => false]);

    $this->seed(RolesPermisosSeeder::class);

    expect($permisoEditado->fresh()->puede_ver)->toBeFalse();

    $nuevoModulo = Modulo::create(['nombre_modulo' => 'Nuevo modulo']);

    $this->seed(RolesPermisosSeeder::class);

    expect($permisoRol($superAdmin, $nuevoModulo)?->puede_ver)->toBeTrue()
        ->and($permisoRol($gerente, $nuevoModulo)?->puede_ver)->toBeFalse()
        ->and($permisoRol($cajero, $nuevoModulo)?->puede_ver)->toBeFalse();

    expect(RolPermiso::where('id_rol', $superAdmin->id)->count())->toBe(Modulo::count());
});
