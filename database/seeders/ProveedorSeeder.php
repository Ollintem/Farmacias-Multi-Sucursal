<?php

namespace Database\Seeders;

use App\Models\Proveedor;
use Illuminate\Database\Seeder;

class ProveedorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (
            [
                [
                    'nombre_proveedor' => 'Farmacias Rivera',
                    'direccion' => 'Av. Juárez 123, Col. Centro, CDMX',
                    'unidad_entrega' => 'Caja',
                    'telefono' => '5551234567',
                    'correo' => 'contacto@rivera.mx',
                ],
                [
                    'nombre_proveedor' => 'Dist. del Centro',
                    'direccion' => 'Calle Hidalgo 45, Toluca, EdoMex',
                    'unidad_entrega' => 'Paquete',
                    'telefono' => '7224567890',
                    'correo' => 'ventas@distcentro.mx',
                ],
                [
                    'nombre_proveedor' => 'MediSur',
                    'direccion' => 'Blvd. Díaz Ordaz 789, Puebla',
                    'unidad_entrega' => 'Granel',
                    'telefono' => '2223456789',
                    'correo' => 'pedidos@medisur.mx',
                ],
            ] as $proveedor
        ) {
            Proveedor::updateOrCreate(
                ['nombre_proveedor' => $proveedor['nombre_proveedor']],
                $proveedor
            );
        }
    }
}
