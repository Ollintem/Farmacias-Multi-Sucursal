<?php

use App\Models\Modulo;
use App\Models\Permiso;
use App\Models\PermisoActivado;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('permite al superadmin crear un supervisor', function () {
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Norte',
        'direccion' => 'Calle Norte 123',
        'hora_apertura' => '08:00:00',
        'hora_cierre' => '20:00:00',
    ]);

    $rolSuperAdmin = Rol::create([
        'tipo_rol' => 'SuperAdmin',
        'descripcion' => 'Acceso total al sistema',
    ]);

    $rolSupervisor = Rol::create([
        'tipo_rol' => 'Supervisor',
        'descripcion' => 'Gestiona personal y sucursal',
    ]);

    $superAdmin = User::create([
        'nombre' => 'Admin',
        'apellido' => 'Sistema',
        'nombre_usuario' => 'admin_sistema',
        'email' => 'admin@sistema.test',
        'password' => Hash::make('password123'),
        'es_activo' => true,
        'id_rol' => $rolSuperAdmin->id,
        'id_sucursal' => $sucursal->id,
    ]);

    $response = $this->actingAs($superAdmin)
        ->post(route('usuarios.store'), [
            'nombre' => 'Ana',
            'apellido' => 'Lopez',
            'nombre_usuario' => 'ana_supervisor',
            'email' => 'ana@farmacia.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'id_rol' => $rolSupervisor->id,
            'id_sucursal' => $sucursal->id,
            'es_activo' => true,
        ]);

    $response->assertRedirect(route('usuarios.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('usuarios', [
        'email' => 'ana@farmacia.test',
        'nombre_usuario' => 'ana_supervisor',
        'id_rol' => $rolSupervisor->id,
        'id_sucursal' => $sucursal->id,
    ]);
});

it('muestra el formulario de edición con los datos y permisos del usuario', function () {
    $rol = Rol::create(['tipo_rol' => 'Cajero', 'descripcion' => 'Ventas y cobros']);
    $modulo = Modulo::create(['nombre_modulo' => 'Caja']);
    $permiso = Permiso::create(['tipo_permiso' => 'Mostrar', 'descripcion' => 'Permite ver registros']);

    $admin = User::create([
        'nombre' => 'Admin',
        'apellido' => 'Sistema',
        'nombre_usuario' => 'admin_sistema',
        'email' => 'admin@sistema.test',
        'password' => Hash::make('password123'),
        'es_activo' => true,
        'id_rol' => $rol->id,
    ]);

    $usuario = User::create([
        'nombre' => 'Ana',
        'apellido' => 'Lopez',
        'nombre_usuario' => 'ana_cajera',
        'email' => 'ana@farmacia.test',
        'password' => Hash::make('password123'),
        'es_activo' => true,
        'id_rol' => $rol->id,
    ]);

    PermisoActivado::create([
        'id_usuario' => $usuario->id,
        'id_modulo' => $modulo->id,
        'id_permiso' => $permiso->id,
        'es_activo' => true,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('usuarios.edit', $usuario));

    $response->assertOk()
        ->assertSee('ana_cajera')
        ->assertSee('Permisos por módulo')
        ->assertSee($modulo->nombre_modulo)
        ->assertSee($permiso->tipo_permiso);
});

it('impide eliminar al propio usuario o a un SuperAdmin', function () {
    $rolSuperAdmin = Rol::create(['tipo_rol' => 'SuperAdmin', 'descripcion' => 'Acceso total']);
    $rolCajero = Rol::create(['tipo_rol' => 'Cajero', 'descripcion' => 'Ventas y cobros']);

    $admin = User::create([
        'nombre' => 'Admin',
        'apellido' => 'Sistema',
        'nombre_usuario' => 'admin_sistema',
        'email' => 'admin@sistema.test',
        'password' => Hash::make('password123'),
        'es_activo' => true,
        'id_rol' => $rolSuperAdmin->id,
    ]);

    $this->actingAs($admin)
        ->delete(route('usuarios.delete', $admin))
        ->assertRedirect(route('usuarios.index'))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('usuarios', ['id' => $admin->id]);

    $otroSuperAdmin = User::create([
        'nombre' => 'Otro',
        'apellido' => 'Admin',
        'nombre_usuario' => 'otro_admin',
        'email' => 'otro@sistema.test',
        'password' => Hash::make('password123'),
        'es_activo' => true,
        'id_rol' => $rolSuperAdmin->id,
    ]);

    $this->actingAs($admin)
        ->delete(route('usuarios.delete', $otroSuperAdmin))
        ->assertRedirect(route('usuarios.index'))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('usuarios', ['id' => $otroSuperAdmin->id]);

    $cajero = User::create([
        'nombre' => 'Ana',
        'apellido' => 'Lopez',
        'nombre_usuario' => 'ana_cajera',
        'email' => 'ana@farmacia.test',
        'password' => Hash::make('password123'),
        'es_activo' => true,
        'id_rol' => $rolCajero->id,
    ]);

    $this->actingAs($admin)
        ->delete(route('usuarios.delete', $cajero))
        ->assertRedirect(route('usuarios.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('usuarios', ['id' => $cajero->id]);
});

it('actualiza los datos del usuario y sus permisos granulares', function () {
    $rol = Rol::create(['tipo_rol' => 'Cajero', 'descripcion' => 'Ventas y cobros']);
    $modulo = Modulo::create(['nombre_modulo' => 'Caja']);
    $mostrar = Permiso::create(['tipo_permiso' => 'Mostrar', 'descripcion' => 'Permite ver registros']);
    $crear = Permiso::create(['tipo_permiso' => 'Crear', 'descripcion' => 'Permite crear registros']);

    $admin = User::create([
        'nombre' => 'Admin',
        'apellido' => 'Sistema',
        'nombre_usuario' => 'admin_sistema',
        'email' => 'admin@sistema.test',
        'password' => Hash::make('password123'),
        'es_activo' => true,
        'id_rol' => $rol->id,
    ]);

    $usuario = User::create([
        'nombre' => 'Ana',
        'apellido' => 'Lopez',
        'nombre_usuario' => 'ana_cajera',
        'email' => 'ana@farmacia.test',
        'password' => Hash::make('password123'),
        'es_activo' => true,
        'id_rol' => $rol->id,
    ]);

    $response = $this->actingAs($admin)
        ->put(route('usuarios.update', $usuario), [
            'nombre' => 'Ana María',
            'apellido' => 'Lopez',
            'nombre_usuario' => 'ana_cajera',
            'email' => 'ana@farmacia.test',
            'id_rol' => $rol->id,
            'id_sucursal' => null,
            'es_activo' => '1',
            'permisos' => [$modulo->id => [$mostrar->id]],
        ]);

    $response->assertRedirect(route('usuarios.edit', $usuario))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('usuarios', [
        'id' => $usuario->id,
        'nombre' => 'Ana María',
    ]);

    $this->assertDatabaseHas('permisos_activados', [
        'id_usuario' => $usuario->id,
        'id_modulo' => $modulo->id,
        'id_permiso' => $mostrar->id,
        'es_activo' => true,
    ]);

    $this->assertDatabaseHas('permisos_activados', [
        'id_usuario' => $usuario->id,
        'id_modulo' => $modulo->id,
        'id_permiso' => $crear->id,
        'es_activo' => false,
    ]);
});

it('muestra los permisos activados del usuario en modo solo lectura', function () {
    $rol = Rol::create(['tipo_rol' => 'Cajero', 'descripcion' => 'Ventas y cobros']);
    $modulo = Modulo::create(['nombre_modulo' => 'Caja']);
    $mostrar = Permiso::create(['tipo_permiso' => 'Mostrar', 'descripcion' => 'Permite ver registros']);
    $crear = Permiso::create(['tipo_permiso' => 'Crear', 'descripcion' => 'Permite crear registros']);

    $admin = User::create([
        'nombre' => 'Admin',
        'apellido' => 'Sistema',
        'nombre_usuario' => 'admin_sistema',
        'email' => 'admin@sistema.test',
        'password' => Hash::make('password123'),
        'es_activo' => true,
        'id_rol' => $rol->id,
    ]);

    $usuario = User::create([
        'nombre' => 'Ana',
        'apellido' => 'Lopez',
        'nombre_usuario' => 'ana_cajera',
        'email' => 'ana@farmacia.test',
        'password' => Hash::make('password123'),
        'es_activo' => true,
        'id_rol' => $rol->id,
    ]);

    PermisoActivado::create([
        'id_usuario' => $usuario->id,
        'id_modulo' => $modulo->id,
        'id_permiso' => $mostrar->id,
        'es_activo' => true,
    ]);

    PermisoActivado::create([
        'id_usuario' => $usuario->id,
        'id_modulo' => $modulo->id,
        'id_permiso' => $crear->id,
        'es_activo' => false,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('usuarios.show', $usuario));

    $response->assertOk()
        ->assertSee('Ver usuario')
        ->assertSee('Permisos activados')
        ->assertSee($modulo->nombre_modulo)
        ->assertSee($mostrar->tipo_permiso)
        ->assertSee('Editar permisos')
        ->assertDontSee('Guardar cambios');
});

it('muestra el modo edición con controles para modificar los permisos', function () {
    $rol = Rol::create(['tipo_rol' => 'Cajero', 'descripcion' => 'Ventas y cobros']);

    $admin = User::create([
        'nombre' => 'Admin',
        'apellido' => 'Sistema',
        'nombre_usuario' => 'admin_sistema',
        'email' => 'admin@sistema.test',
        'password' => Hash::make('password123'),
        'es_activo' => true,
        'id_rol' => $rol->id,
    ]);

    $usuario = User::create([
        'nombre' => 'Ana',
        'apellido' => 'Lopez',
        'nombre_usuario' => 'ana_cajera',
        'email' => 'ana@farmacia.test',
        'password' => Hash::make('password123'),
        'es_activo' => true,
        'id_rol' => $rol->id,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('usuarios.edit', $usuario));

    $response->assertOk()
        ->assertSee('Editar usuario')
        ->assertSee('Guardar cambios')
        ->assertSee('Activar todo');
});
