<?php

namespace Database\Seeders;

use App\Models\Lote;
use App\Models\Proveedor;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class LoteSeeder extends Seeder
{
    public function run(): void
    {
        $proveedor = Proveedor::first()
            ?? Proveedor::create([
                'nombre_proveedor' => 'Proveedor Demo',
                'rfc' => 'DEMO123456ABC',
                'telefono' => '555-1234',
                'email' => 'demo@proveedor.com',
                'direccion' => 'Calle Demo 123',
            ]);

        Lote::create([
            'folio' => 'LOTE-001',
            'stock_lote' => 100,
            'id_proveedor' => $proveedor->id,
            'entregado_en' => now(),
            'fecha_caducidad' => Carbon::now()->addMonths(12),
            'fecha_de_caducidad' => Carbon::now()->addMonths(12),
        ]);

        Lote::create([
            'folio' => 'LOTE-002',
            'stock_lote' => 50,
            'id_proveedor' => $proveedor->id,
            'entregado_en' => now(),
            'fecha_caducidad' => Carbon::now()->addMonths(6),
            'fecha_de_caducidad' => Carbon::now()->addMonths(6),
        ]);
    }
}
