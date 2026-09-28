<?php

use App\Models\Rol;
use App\Models\User;

it('loads the lotes and expiry page', function () {
    $rol = Rol::firstOrCreate(['tipo_rol' => 'SuperAdmin'], ['descripcion' => 'Acceso total']);

    $user = User::factory()->create(['id_rol' => $rol->id]);

    $this->actingAs($user)
        ->get(route('lotes.index'))
        ->assertOk();
});
