<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rolSuperAdmin = Rol::where('tipo_rol', 'SuperAdmin')->first();
        $rolCajero = Rol::where('tipo_rol', 'Cajero')->first();

        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'nombre' => 'Admin',
                'apellido' => 'Sistema',
                'id_sucursal' => 1,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'es_activo' => true,
                'id_rol' => $rolSuperAdmin?->id,
            ]
        );
    }
}
