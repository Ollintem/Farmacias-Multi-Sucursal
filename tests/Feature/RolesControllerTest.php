<?php

use App\Models\Rol;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

function crearAdminConRol(string $tipoRol = 'SuperAdmin'): User
{
    $rol = Rol::create(['tipo_rol' => $tipoRol.'_'.str()->random(4), 'descripcion' => $tipoRol]);

    if ($tipoRol === 'SuperAdmin') {
        $rol->update(['tipo_rol' => 'SuperAdmin']);
    }

    return User::create([
        'nombre' => 'Admin',
        'apellido' => 'Roles',
        'nombre_usuario' => 'admin_roles_'.str()->random(5),
        'email' => 'admin_roles_'.str()->random(5).'@farmacia.test',
        'password' => Hash::make('password123'),
        'es_activo' => true,
        'id_rol' => $rol->id,
    ]);
}

it('muestra el listado de roles', function () {
    $admin = crearAdminConRol();
    Rol::create(['tipo_rol' => 'Almacenista', 'descripcion' => 'Gestiona almacén']);

    $this->actingAs($admin)
        ->get(route('roles.index'))
        ->assertOk()
        ->assertSee('Roles')
        ->assertSee('Almacenista');
});

it('registra un rol nuevo', function () {
    $admin = crearAdminConRol();

    $response = $this->actingAs($admin)
        ->post(route('roles.store'), [
            'tipo_rol' => 'Supervisor',
            'descripcion' => 'Supervisa sucursal',
        ]);

    $response->assertRedirect(route('roles.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('roles', ['tipo_rol' => 'Supervisor']);
});

it('impide eliminar el rol SuperAdmin o un rol con usuarios', function () {
    $admin = crearAdminConRol();
    $superAdmin = Rol::where('tipo_rol', 'SuperAdmin')->firstOrFail();

    $this->actingAs($admin)
        ->delete(route('roles.destroy', $superAdmin))
        ->assertRedirect(route('roles.index'))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('roles', ['id' => $superAdmin->id]);

    $rolConUsuarios = Rol::create(['tipo_rol' => 'CajeroTest', 'descripcion' => 'Caja']);
    User::create([
        'nombre' => 'Ana',
        'apellido' => 'Lopez',
        'nombre_usuario' => 'ana_'.$admin->id,
        'email' => 'ana_'.$admin->id.'@farmacia.test',
        'password' => Hash::make('password123'),
        'es_activo' => true,
        'id_rol' => $rolConUsuarios->id,
    ]);

    $this->actingAs($admin)
        ->delete(route('roles.destroy', $rolConUsuarios))
        ->assertRedirect(route('roles.index'))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('roles', ['id' => $rolConUsuarios->id]);
});
