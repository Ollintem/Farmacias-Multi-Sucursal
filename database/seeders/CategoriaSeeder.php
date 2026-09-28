<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (
            [
                'Analgésicos',
                'Antibióticos',
                'Respiratorios',
                'Gastrointestinales',
                'Vitaminas',
            ] as $nombre
        ) {
            Categoria::updateOrCreate(['nombre' => $nombre]);
        }
    }
}
