<?php

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
