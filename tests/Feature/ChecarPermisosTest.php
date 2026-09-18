<?php

use App\Models\Modulo;
use App\Models\Permiso;
use App\Models\PermisoActivado;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

function crearUsuarioConRol(string $tipoRol): User
{
    $rol = Rol::create(['tipo_rol' => $tipoRol, 'descripcion' => $tipoRol]);

    return User::create([
        'nombre' => 'Test',
        'apellido' => 'User',
        'nombre_usuario' => 'test_'.str()->random(6),
        'email' => 'test_'.str()->random(6).'@farmacia.test',
        'password' => Hash::make('password123'),
        'es_activo' => true,
        'id_rol' => $rol->id,
    ]);
}

function otorgarPermiso(User $usuario, string $nombreModulo, string $tipoPermiso): void
{
    $modulo = Modulo::create(['nombre_modulo' => $nombreModulo]);
    $permiso = Permiso::create(['tipo_permiso' => $tipoPermiso, 'descripcion' => $tipoPermiso]);

    PermisoActivado::create([
        'id_usuario' => $usuario->id,
        'id_modulo' => $modulo->id,
        'id_permiso' => $permiso->id,
        'es_activo' => true,
    ]);
}

it('bloquea con 403 cuando el usuario no tiene el permiso del módulo', function () {
    $usuario = crearUsuarioConRol('Cajero');
    otorgarPermiso($usuario, 'Caja', 'Mostrar');

    $this->actingAs($usuario)
        ->get(route('usuarios.index'))
        ->assertForbidden();
});

it('permite el acceso cuando el usuario tiene el permiso del módulo', function () {
    $usuario = crearUsuarioConRol('Cajero');
    otorgarPermiso($usuario, 'Caja', 'Mostrar');
    otorgarPermiso($usuario, 'Usuarios y roles', 'Mostrar');

    $this->actingAs($usuario)
        ->get(route('usuarios.index'))
        ->assertOk();
});

it('deja pasar al SuperAdmin aunque no tenga permisos registrados', function () {
    $usuario = crearUsuarioConRol('SuperAdmin');

    $this->actingAs($usuario)
        ->get(route('usuarios.index'))
        ->assertOk();
});

it('permite el acceso cuando el usuario aún no tiene permisos configurados', function () {
    $usuario = crearUsuarioConRol('Cajero');

    $this->actingAs($usuario)
        ->get(route('usuarios.index'))
        ->assertOk();
});
