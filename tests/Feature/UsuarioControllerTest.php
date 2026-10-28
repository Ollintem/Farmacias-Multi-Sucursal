<?php

use App\Models\Modulo;
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
        'id_rol' => $rolSupervisor->id,
        'id_sucursal' => $sucursal->id,
    ]);
});

it('muestra el formulario de edición con los datos y permisos del usuario', function () {
    $rol = Rol::create(['tipo_rol' => 'Cajero', 'descripcion' => 'Ventas y cobros']);
    $modulo = Modulo::create(['nombre_modulo' => 'Caja']);

    $admin = User::create([
        'nombre' => 'Admin',
        'apellido' => 'Sistema',
        'email' => 'admin@sistema.test',
        'password' => Hash::make('password123'),
        'es_activo' => true,
        'id_rol' => $rol->id,
    ]);

    $usuario = User::create([
        'nombre' => 'Ana',
        'apellido' => 'Lopez',
        'email' => 'ana@farmacia.test',
        'password' => Hash::make('password123'),
        'es_activo' => true,
        'id_rol' => $rol->id,
    ]);

    PermisoActivado::create([
        'id_usuario' => $usuario->id,
        'id_modulo' => $modulo->id,
        'puede_ver' => true,
        'puede_crear' => false,
        'puede_editar' => false,
        'puede_borrar' => false,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('usuarios.edit', $usuario));

    $response->assertOk()
        ->assertSee('ana@farmacia.test')
        ->assertSee(route('usuarios.permisos', $usuario));

    $this->actingAs($admin)
        ->get(route('usuarios.permisos', $usuario))
        ->assertOk()
        ->assertSee('Permisos por módulo')
        ->assertSee($modulo->nombre_modulo)
        ->assertSee('Ver');
});

it('impide eliminar al propio usuario o a un SuperAdmin', function () {
    $rolSuperAdmin = Rol::create(['tipo_rol' => 'SuperAdmin', 'descripcion' => 'Acceso total']);
    $rolCajero = Rol::create(['tipo_rol' => 'Cajero', 'descripcion' => 'Ventas y cobros']);

    $admin = User::create([
        'nombre' => 'Admin',
        'apellido' => 'Sistema',
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

    $admin = User::create([
        'nombre' => 'Admin',
        'apellido' => 'Sistema',
        'email' => 'admin@sistema.test',
        'password' => Hash::make('password123'),
        'es_activo' => true,
        'id_rol' => $rol->id,
    ]);

    $usuario = User::create([
        'nombre' => 'Ana',
        'apellido' => 'Lopez',
        'email' => 'ana@farmacia.test',
        'password' => Hash::make('password123'),
        'es_activo' => true,
        'id_rol' => $rol->id,
    ]);

    $response = $this->actingAs($admin)
        ->put(route('usuarios.update', $usuario), [
            'nombre' => 'Ana María',
            'apellido' => 'Lopez',
            'email' => 'ana@farmacia.test',
            'id_rol' => $rol->id,
            'id_sucursal' => null,
            'es_activo' => '1',
            'permisos' => [$modulo->id => ['ver' => '1']],
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
        'puede_ver' => true,
        'puede_crear' => false,
        'puede_editar' => false,
        'puede_borrar' => false,
    ]);
});

it('muestra los permisos activados del usuario en modo solo lectura', function () {
    $rol = Rol::create(['tipo_rol' => 'Cajero', 'descripcion' => 'Ventas y cobros']);
    $modulo = Modulo::create(['nombre_modulo' => 'Caja']);

    $admin = User::create([
        'nombre' => 'Admin',
        'apellido' => 'Sistema',
        'email' => 'admin@sistema.test',
        'password' => Hash::make('password123'),
        'es_activo' => true,
        'id_rol' => $rol->id,
    ]);

    $usuario = User::create([
        'nombre' => 'Ana',
        'apellido' => 'Lopez',
        'email' => 'ana@farmacia.test',
        'password' => Hash::make('password123'),
        'es_activo' => true,
        'id_rol' => $rol->id,
    ]);

    PermisoActivado::create([
        'id_usuario' => $usuario->id,
        'id_modulo' => $modulo->id,
        'puede_ver' => true,
        'puede_crear' => false,
        'puede_editar' => false,
        'puede_borrar' => false,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('usuarios.permisos', $usuario));

    $response->assertOk()
        ->assertSee($usuario->nombre)
        ->assertSee('Permisos por módulo')
        ->assertSee($modulo->nombre_modulo)
        ->assertSee('Guardar permisos');
});

it('muestra el modo edición con controles para modificar los permisos', function () {
    $rol = Rol::create(['tipo_rol' => 'Cajero', 'descripcion' => 'Ventas y cobros']);

    $admin = User::create([
        'nombre' => 'Admin',
        'apellido' => 'Sistema',
        'email' => 'admin@sistema.test',
        'password' => Hash::make('password123'),
        'es_activo' => true,
        'id_rol' => $rol->id,
    ]);

    $usuario = User::create([
        'nombre' => 'Ana',
        'apellido' => 'Lopez',
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
        ->assertSee(route('usuarios.permisos', $usuario));

    $this->actingAs($admin)
        ->get(route('usuarios.permisos', $usuario))
        ->assertOk()
        ->assertSee('Activar todo');
});

it('renderiza el selector option-pick como una lista flotante', function () {
    $html = view('components.option-pick', [
        'name' => 'id_rol',
        'options' => [1 => 'Cajero'],
        'value' => '',
        'label' => 'Roles',
    ])->render();

    expect($html)
        ->toContain('class="relative"')
        ->toContain('fixed z-20')
        ->toContain('x-transition.opacity')
        ->toContain('farma-pick-scroll')
        ->not->toContain('class="farma-pick-scroll mt-2"');
});

it('muestra el alta de usuario con el selector de rol y campos accesibles', function () {
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Alta',
        'direccion' => 'Calle Alta 1',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $rolSuperAdmin = Rol::create(['tipo_rol' => 'SuperAdmin', 'descripcion' => 'Acceso total']);
    Rol::create(['tipo_rol' => 'Cajero', 'descripcion' => 'Ventas y cobros']);
    Modulo::create(['nombre_modulo' => 'Caja']);

    $admin = User::create([
        'nombre' => 'Admin',
        'apellido' => 'Sistema',
        'email' => 'admin@alta.test',
        'password' => Hash::make('password123'),
        'es_activo' => true,
        'id_rol' => $rolSuperAdmin->id,
        'id_sucursal' => $sucursal->id,
    ]);

    $response = $this->actingAs($admin)->get(route('usuarios.create'));

    $response->assertOk()
        ->assertSee('for="nombre"', false)
        ->assertSee('id="nombre"', false)
        ->assertSee('for="id_rol"', false)
        ->assertSee('name="id_rol"', false)
        ->assertSee('Contraseña')
        ->assertSee('autocomplete="new-password"', false)
        ->assertSee('option-pick-changed', false)
        ->assertSee('aria-modal="true"', false)
        ->assertDontSee('<select name="id_rol"', false);
});

it('guarda los permisos enviados al crear un usuario', function () {
    $sucursal = Sucursal::create([
        'nombre_sucursal' => 'Sucursal Permisos Alta',
        'direccion' => 'Calle Permisos 1',
        'hora_apertura' => '08:00',
        'hora_cierre' => '20:00',
    ]);

    $rolSuperAdmin = Rol::create(['tipo_rol' => 'SuperAdmin', 'descripcion' => 'Acceso total']);
    $rolCajero = Rol::create(['tipo_rol' => 'Cajero', 'descripcion' => 'Ventas y cobros']);
    $modulo = Modulo::create(['nombre_modulo' => 'Caja']);

    $admin = User::create([
        'nombre' => 'Admin',
        'apellido' => 'Sistema',
        'email' => 'admin@permisos.test',
        'password' => Hash::make('password123'),
        'es_activo' => true,
        'id_rol' => $rolSuperAdmin->id,
        'id_sucursal' => $sucursal->id,
    ]);

    $response = $this->actingAs($admin)->post(route('usuarios.store'), [
        'nombre' => 'Ana',
        'apellido' => 'Lopez',
        'email' => 'ana@permisos.test',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'id_rol' => $rolCajero->id,
        'id_sucursal' => $sucursal->id,
        'es_activo' => '1',
        'permisos' => [
            $modulo->id => ['ver' => '1', 'crear' => '1'],
        ],
    ]);

    $response->assertRedirect(route('usuarios.index'))
        ->assertSessionHas('success');

    $usuario = User::where('email', 'ana@permisos.test')->firstOrFail();

    $this->assertDatabaseHas('permisos_activados', [
        'id_usuario' => $usuario->id,
        'id_modulo' => $modulo->id,
        'puede_ver' => true,
        'puede_crear' => true,
        'puede_editar' => false,
        'puede_borrar' => false,
    ]);
});

it('guarda el permiso de corte ciego del usuario', function () {
    $rol = Rol::create(['tipo_rol' => 'Cajero', 'descripcion' => 'Ventas y cobros']);
    $modulo = Modulo::create(['nombre_modulo' => 'Caja']);

    $admin = User::create([
        'nombre' => 'Admin',
        'apellido' => 'Sistema',
        'email' => 'admin.corte@example.test',
        'password' => Hash::make('password123'),
        'es_activo' => true,
        'id_rol' => $rol->id,
    ]);

    $usuario = User::create([
        'nombre' => 'Ana',
        'apellido' => 'Lopez',
        'email' => 'ana.corte@example.test',
        'password' => Hash::make('password123'),
        'es_activo' => true,
        'id_rol' => $rol->id,
    ]);

    $this->actingAs($admin)
        ->put(route('usuarios.updatePermisos', $usuario), [
            'permisos' => [
                $modulo->id => ['ver' => '1'],
            ],
            'corte_ciego' => '1',
        ])
        ->assertRedirect(route('usuarios.permisos', $usuario))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('permisos_activados', [
        'id_usuario' => $usuario->id,
        'id_modulo' => $modulo->id,
        'puede_ver' => true,
    ]);

    $this->assertDatabaseHas('cortes_ciegos', [
        'id_usuarios' => $usuario->id,
        'corte_ciego' => true,
    ]);

    $this->actingAs($admin)
        ->get(route('usuarios.permisos', $usuario))
        ->assertOk()
        ->assertSee('Corte ciego');
});

it('usa el corte ciego del rol cuando el usuario no tiene excepcion', function () {
    $rol = Rol::create([
        'tipo_rol' => 'Cajero',
        'descripcion' => 'Ventas y cobros',
        'corte_ciego' => true,
    ]);

    $usuario = User::create([
        'nombre' => 'Luis',
        'apellido' => 'Perez',
        'email' => 'luis.corte@example.test',
        'password' => Hash::make('password123'),
        'es_activo' => true,
        'id_rol' => $rol->id,
    ]);

    expect($usuario->tieneCorteCiego())->toBeTrue()
        ->and($usuario->permisosHabilitados('corte_ciego', 'Caja'))->toBeTrue();

    $usuario->corteCiego()->create(['corte_ciego' => false]);

    expect($usuario->fresh()->tieneCorteCiego())->toBeFalse();
});
