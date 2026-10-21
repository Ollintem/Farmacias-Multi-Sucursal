<?php

use App\Models\Modulo;
use App\Models\Permiso;
use App\Models\PermisoActivado;
use App\Models\Rol;
use App\Models\RolPermiso;
use App\Models\User;
use Database\Seeders\ModuloSeeder;
use Database\Seeders\PermisoSeeder;
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

function crearPermisoDeModulo(Modulo $modulo, string $accion): Permiso
{
    return Permiso::create([
        'id_modulo' => $modulo->id,
        'accion' => $accion,
    ]);
}

function obtenerPermisoDe(string $nombreModulo, string $accion): Permiso
{
    return Permiso::query()
        ->whereHas('modulo', fn ($query) => $query->where('nombre_modulo', $nombreModulo))
        ->where('accion', $accion)
        ->firstOrFail();
}

it('muestra la configuracion de permisos del rol seleccionado', function () {
    $this->seed([
        RolSeeder::class,
        ModuloSeeder::class,
        PermisoSeeder::class,
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
        ->assertSee('Guardar permisos');
});

it('actualiza solo el rol seleccionado sin afectar otros roles', function () {
    $rolAdmin = crearRolParaPermisos('SuperAdmin');
    $gerente = crearRolParaPermisos('Gerente');
    $cajero = crearRolParaPermisos('Cajero');
    $admin = crearUsuarioParaPermisos($rolAdmin);

    $modulo = Modulo::create(['nombre_modulo' => 'Inventario']);
    $permisos = collect(['ver', 'crear', 'editar', 'borrar'])
        ->mapWithKeys(fn (string $accion): array => [$accion => crearPermisoDeModulo($modulo, $accion)]);

    RolPermiso::create([
        'id_rol' => $cajero->id,
        'id_permiso' => $permisos['ver']->id,
        'activo' => true,
    ]);

    $this->actingAs($admin)
        ->put(route('roles.permisos.update', $gerente), [
            'permisos' => [$permisos['ver']->id => '1'],
        ])
        ->assertRedirect(route('roles.permisos', ['rol' => $gerente->id]))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('roles_permisos', [
        'id_rol' => $gerente->id,
        'id_permiso' => $permisos['ver']->id,
        'activo' => true,
    ]);

    $this->assertDatabaseHas('roles_permisos', [
        'id_rol' => $gerente->id,
        'id_permiso' => $permisos['crear']->id,
        'activo' => false,
    ]);

    $this->assertDatabaseHas('roles_permisos', [
        'id_rol' => $cajero->id,
        'id_permiso' => $permisos['ver']->id,
        'activo' => true,
    ]);

    $this->assertDatabaseMissing('roles_permisos', [
        'id_rol' => $cajero->id,
        'id_permiso' => $permisos['crear']->id,
    ]);

    $this->actingAs($admin)
        ->put(route('roles.permisos.update', $gerente), [
            'permisos' => [$permisos['crear']->id => '1'],
        ])
        ->assertRedirect(route('roles.permisos', ['rol' => $gerente->id]));

    expect($admin->fresh()->id_rol)->toBe($rolAdmin->id);

    $this->assertDatabaseCount('roles_permisos', 5);
    $this->assertDatabaseHas('roles_permisos', [
        'id_rol' => $gerente->id,
        'id_permiso' => $permisos['ver']->id,
        'activo' => false,
    ]);
    $this->assertDatabaseHas('roles_permisos', [
        'id_rol' => $gerente->id,
        'id_permiso' => $permisos['crear']->id,
        'activo' => true,
    ]);
});

it('impide modificar permisos sin autorizacion de edicion', function () {
    $rolCajero = crearRolParaPermisos('Cajero');
    $rolGerente = crearRolParaPermisos('Gerente');
    $usuario = crearUsuarioParaPermisos($rolCajero);

    $moduloUsuarios = Modulo::create(['nombre_modulo' => 'Usuarios y roles']);
    $permisoVer = crearPermisoDeModulo($moduloUsuarios, 'ver');
    $permisoEditar = crearPermisoDeModulo($moduloUsuarios, 'editar');

    RolPermiso::create([
        'id_rol' => $rolCajero->id,
        'id_permiso' => $permisoVer->id,
        'activo' => true,
    ]);

    RolPermiso::create([
        'id_rol' => $rolCajero->id,
        'id_permiso' => $permisoEditar->id,
        'activo' => false,
    ]);

    $moduloInventario = Modulo::create(['nombre_modulo' => 'Inventario']);
    $permisoInventario = crearPermisoDeModulo($moduloInventario, 'ver');

    $this->actingAs($usuario)
        ->put(route('roles.permisos.update', $rolGerente), [
            'permisos' => [$permisoInventario->id => '1'],
        ])
        ->assertForbidden();

    $this->assertDatabaseMissing('roles_permisos', [
        'id_rol' => $rolGerente->id,
        'id_permiso' => $permisoInventario->id,
    ]);
});

it('impide restringir el rol SuperAdmin', function () {
    $superAdmin = crearRolParaPermisos('SuperAdmin');
    $usuario = crearUsuarioParaPermisos($superAdmin);
    $modulo = Modulo::create(['nombre_modulo' => 'Inventario']);
    $permiso = crearPermisoDeModulo($modulo, 'ver');

    $this->actingAs($usuario)
        ->put(route('roles.permisos.update', $superAdmin), [
            'permisos' => [$permiso->id => '1'],
        ])
        ->assertSessionHas('error');

    $this->assertDatabaseMissing('roles_permisos', [
        'id_rol' => $superAdmin->id,
        'id_permiso' => $permiso->id,
    ]);
});

it('muestra la configuracion en solo lectura cuando no puede editar', function () {
    $rol = crearRolParaPermisos('Cajero');
    $usuario = crearUsuarioParaPermisos($rol);
    $modulo = Modulo::create(['nombre_modulo' => 'Usuarios y roles']);
    $permisoVer = crearPermisoDeModulo($modulo, 'ver');
    $permisoEditar = crearPermisoDeModulo($modulo, 'editar');

    RolPermiso::create([
        'id_rol' => $rol->id,
        'id_permiso' => $permisoVer->id,
        'activo' => true,
    ]);

    RolPermiso::create([
        'id_rol' => $rol->id,
        'id_permiso' => $permisoEditar->id,
        'activo' => false,
    ]);

    $this->actingAs($usuario)
        ->get(route('roles.permisos'))
        ->assertOk()
        ->assertSee('Solo lectura');
});

it('el rol restringe el acceso aunque exista una excepcion individual', function () {
    $rol = crearRolParaPermisos('Cajero');
    $usuario = crearUsuarioParaPermisos($rol);
    $modulo = Modulo::create(['nombre_modulo' => 'Inventario']);
    $permiso = crearPermisoDeModulo($modulo, 'ver');

    RolPermiso::create([
        'id_rol' => $rol->id,
        'id_permiso' => $permiso->id,
        'activo' => false,
    ]);

    PermisoActivado::create([
        'id_usuario' => $usuario->id,
        'id_modulo' => $modulo->id,
        'puede_ver' => true,
        'puede_crear' => true,
        'puede_editar' => true,
        'puede_borrar' => true,
    ]);

    expect($usuario->permisosHabilitados('ver', 'inventario'))->toBeFalse();

    $this->actingAs($usuario)
        ->get(route('roles.permisos'))
        ->assertForbidden();
});

it('el rol autoriza sin necesidad de una excepcion individual', function () {
    $rol = crearRolParaPermisos('Cajero');
    $usuario = crearUsuarioParaPermisos($rol);
    $modulo = Modulo::create(['nombre_modulo' => 'Dashboard']);
    $permiso = crearPermisoDeModulo($modulo, 'ver');

    RolPermiso::create([
        'id_rol' => $rol->id,
        'id_permiso' => $permiso->id,
        'activo' => true,
    ]);

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
    $permiso = crearPermisoDeModulo($modulo, 'ver');

    RolPermiso::create([
        'id_rol' => $rol->id,
        'id_permiso' => $permiso->id,
        'activo' => true,
    ]);

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
        PermisoSeeder::class,
        RolesPermisosSeeder::class,
    ]);

    $totalPermisos = Permiso::count();
    $superAdmin = Rol::where('tipo_rol', 'SuperAdmin')->firstOrFail();
    $gerente = Rol::where('tipo_rol', 'Gerente')->firstOrFail();
    $cajero = Rol::where('tipo_rol', 'Cajero')->firstOrFail();

    expect($totalPermisos)->toBeGreaterThan(0)
        ->and(RolPermiso::where('id_rol', $superAdmin->id)->count())->toBe($totalPermisos)
        ->and(RolPermiso::where('id_rol', $superAdmin->id)->where('activo', true)->count())->toBe($totalPermisos)
        ->and(RolPermiso::where('id_rol', $gerente->id)->count())->toBe($totalPermisos)
        ->and(RolPermiso::where('id_rol', $cajero->id)->count())->toBe($totalPermisos);

    expect(RolPermiso::where('id_rol', $gerente->id)
        ->where('id_permiso', obtenerPermisoDe('Caja', 'ver')->id)
        ->value('activo'))->toBeTrue();

    expect(RolPermiso::where('id_rol', $gerente->id)
        ->where('id_permiso', obtenerPermisoDe('Sucursales', 'ver')->id)
        ->value('activo'))->toBeFalse();

    expect(RolPermiso::where('id_rol', $gerente->id)
        ->where('id_permiso', obtenerPermisoDe('Usuarios y roles', 'ver')->id)
        ->value('activo'))->toBeFalse();

    expect(RolPermiso::where('id_rol', $gerente->id)
        ->where('id_permiso', obtenerPermisoDe('Usuarios y roles', 'borrar')->id)
        ->value('activo'))->toBeFalse();

    expect(RolPermiso::where('id_rol', $cajero->id)
        ->where('id_permiso', obtenerPermisoDe('Punto de venta', 'ver')->id)
        ->value('activo'))->toBeTrue();

    expect(RolPermiso::where('id_rol', $cajero->id)
        ->where('id_permiso', obtenerPermisoDe('Inventario', 'ver')->id)
        ->value('activo'))->toBeTrue();

    expect(RolPermiso::where('id_rol', $cajero->id)
        ->where('id_permiso', obtenerPermisoDe('Caja', 'ver')->id)
        ->value('activo'))->toBeTrue();

    expect(RolPermiso::where('id_rol', $cajero->id)
        ->where('id_permiso', obtenerPermisoDe('Dashboard', 'ver')->id)
        ->value('activo'))->toBeFalse();

    expect(RolPermiso::where('id_rol', $cajero->id)
        ->where('id_permiso', obtenerPermisoDe('Usuarios y roles', 'ver')->id)
        ->value('activo'))->toBeFalse();

    $permisoEditado = RolPermiso::where('id_rol', $gerente->id)
        ->where('id_permiso', obtenerPermisoDe('Inventario', 'ver')->id)
        ->firstOrFail();

    $permisoEditado->update(['activo' => false]);

    $this->seed(RolesPermisosSeeder::class);

    expect($permisoEditado->fresh()->activo)->toBeFalse();

    $nuevoModulo = Modulo::create(['nombre_modulo' => 'Nuevo modulo']);
    $nuevoPermiso = crearPermisoDeModulo($nuevoModulo, 'ver');

    $this->seed(RolesPermisosSeeder::class);

    expect(RolPermiso::where('id_rol', $superAdmin->id)
        ->where('id_permiso', $nuevoPermiso->id)->value('activo'))->toBeTrue()
        ->and(RolPermiso::where('id_rol', $gerente->id)
            ->where('id_permiso', $nuevoPermiso->id)->value('activo'))->toBeFalse()
        ->and(RolPermiso::where('id_rol', $cajero->id)
            ->where('id_permiso', $nuevoPermiso->id)->value('activo'))->toBeFalse();

    expect(RolPermiso::where('id_rol', $superAdmin->id)->count())->toBe(Permiso::count());
});
