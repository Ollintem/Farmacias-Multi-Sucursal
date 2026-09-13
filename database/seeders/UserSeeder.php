<?php

namespace Database\Seeders;

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
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'nombre' => 'Admin',
                'apellido' => 'Sistema',
                'nombre_usuario' => 'admin',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'es_activo' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'nombre' => 'Usuario',
                'apellido' => 'Pruebas',
                'nombre_usuario' => 'test',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'es_activo' => true,
            ]
        );
    }
}
