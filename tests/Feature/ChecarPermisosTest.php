<?php

use App\Models\Modulo;
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

/**
 * @param  array{puede_ver?: bool, puede_crear?: bool, puede_editar?: bool, puede_borrar?: bool}  $flags
 */
function otorgarPermiso(User $usuario, string $nombreModulo, array $flags = []): void
{
    $modulo = Modulo::firstOrCreate(['nombre_modulo' => $nombreModulo]);

    PermisoActivado::updateOrCreate(
        [
            'id_usuario' => $usuario->id,
            'id_modulo' => $modulo->id,
        ],
        [
            'puede_ver' => $flags['puede_ver'] ?? true,
            'puede_crear' => $flags['puede_crear'] ?? false,
            'puede_editar' => $flags['puede_editar'] ?? false,
            'puede_borrar' => $flags['puede_borrar'] ?? false,
        ],
    );
}

it('bloquea con 403 cuando el usuario no tiene el permiso del módulo', function () {
    $usuario = crearUsuarioConRol('Cajero');
    otorgarPermiso($usuario, 'Caja');

    $this->actingAs($usuario)
        ->get(route('usuarios.index'))
        ->assertForbidden();
});

it('permite el acceso cuando el usuario tiene el permiso del módulo', function () {
    $usuario = crearUsuarioConRol('Cajero');
    otorgarPermiso($usuario, 'Caja');
    otorgarPermiso($usuario, 'Usuarios y roles');

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
