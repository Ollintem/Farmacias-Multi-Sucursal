<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Inventario;
use App\Models\Lote;
use App\Models\PresentacionProducto;
use App\Models\Producto;
use App\Models\ProductoPresentacion;
use App\Models\Proveedor;
use App\Models\Sucursal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductoPruebaSeeder extends Seeder
{
    /**
     * Registros de prueba: productos + precios por presentación + lotes + inventario.
     * Idempotente: usa updateOrCreate por codigo_barras / folio.
     */
    public function run(): void
    {
        $caja = PresentacionProducto::where('presentacion', 'caja')->firstOrFail();
        $blister = PresentacionProducto::where('presentacion', 'blister')->firstOrFail();

        $cat = fn (string $nombre): ?int => Categoria::where('nombre', $nombre)->first()?->id;
        $prov = fn (string $nombre): ?int => Proveedor::where('nombre_proveedor', $nombre)->first()?->id;
        $sucCentro = Sucursal::where('nombre_sucursal', 'Sucursal Centro')->firstOrFail();
        $sucNorte = Sucursal::where('nombre_sucursal', 'Sucursal Norte')->firstOrFail();

        $productos = [
            [
                'codigo_barras' => '7501000100018',
                'nombre_producto' => 'Paracetamol 500mg',
                'descripcion' => 'Analgésico y antipirético. Caja con 20 tabletas.',
                'categoria' => 'Analgésicos',
                'presentacion_principal' => 'caja',
                'precio_base' => 42.50,
                'es_controlado' => false,
                'precios' => [
                    ['presentacion' => 'caja', 'precio' => 85.00, 'unidades' => 20],
                    ['presentacion' => 'blister', 'precio' => 45.00, 'unidades' => 10],
                ],
                'lote' => ['folio' => 'LT-PARA-001', 'proveedor' => 'Farmacias Rivera', 'caducidad_meses' => 18],
                'inventario' => ['centro' => 60, 'norte' => 40],
            ],
            [
                'codigo_barras' => '7501000100025',
                'nombre_producto' => 'Ibuprofeno 400mg',
                'descripcion' => 'Antiinflamatorio no esteroideo. Caja con 10 cápsulas.',
                'categoria' => 'Analgésicos',
                'presentacion_principal' => 'caja',
                'precio_base' => 38.00,
                'es_controlado' => false,
                'precios' => [
                    ['presentacion' => 'caja', 'precio' => 68.00, 'unidades' => 10],
                    ['presentacion' => 'blister', 'precio' => 35.00, 'unidades' => 5],
                ],
                'lote' => ['folio' => 'LT-IBU-001', 'proveedor' => 'Dist. del Centro', 'caducidad_meses' => 24],
                'inventario' => ['centro' => 50, 'norte' => 30],
            ],
            [
                'codigo_barras' => '7501000100032',
                'nombre_producto' => 'Amoxicilina 500mg',
                'descripcion' => 'Antibiótico de amplio espectro. Requiere receta. Caja con 12 cápsulas.',
                'categoria' => 'Antibióticos',
                'presentacion_principal' => 'caja',
                'precio_base' => 95.00,
                'es_controlado' => true,
                'precios' => [
                    ['presentacion' => 'caja', 'precio' => 120.00, 'unidades' => 12],
                ],
                'lote' => ['folio' => 'LT-AMOX-001', 'proveedor' => 'MediSur', 'caducidad_meses' => 12],
                'inventario' => ['centro' => 36, 'norte' => 24],
            ],
            [
                'codigo_barras' => '7501000100049',
                'nombre_producto' => 'Loratadina 10mg',
                'descripcion' => 'Antihistamínico para alergias respiratorias. Caja con 10 tabletas.',
                'categoria' => 'Respiratorios',
                'presentacion_principal' => 'blister',
                'precio_base' => 32.00,
                'es_controlado' => false,
                'precios' => [
                    ['presentacion' => 'caja', 'precio' => 55.00, 'unidades' => 10],
                    ['presentacion' => 'blister', 'precio' => 30.00, 'unidades' => 5],
                ],
                'lote' => ['folio' => 'LT-LORA-001', 'proveedor' => 'Farmacias Rivera', 'caducidad_meses' => 20],
                'inventario' => ['centro' => 45, 'norte' => 45],
            ],
            [
                'codigo_barras' => '7501000100056',
                'nombre_producto' => 'Omeprazol 20mg',
                'descripcion' => 'Inhibidor de bomba de protones. Caja con 14 cápsulas.',
                'categoria' => 'Gastrointestinales',
                'presentacion_principal' => 'caja',
                'precio_base' => 58.00,
                'es_controlado' => false,
                'precios' => [
                    ['presentacion' => 'caja', 'precio' => 98.00, 'unidades' => 14],
                    ['presentacion' => 'blister', 'precio' => 52.00, 'unidades' => 7],
                ],
                'lote' => ['folio' => 'LT-OME-001', 'proveedor' => 'Dist. del Centro', 'caducidad_meses' => 15],
                'inventario' => ['centro' => 28, 'norte' => 14],
            ],
            [
                'codigo_barras' => '7501000100063',
                'nombre_producto' => 'Vitamina C 1g efervescente',
                'descripcion' => 'Suplemento vitamínico. Tubo con 10 tabletas efervescentes.',
                'categoria' => 'Vitaminas',
                'presentacion_principal' => 'caja',
                'precio_base' => 72.00,
                'es_controlado' => false,
                'precios' => [
                    ['presentacion' => 'caja', 'precio' => 72.00, 'unidades' => 10],
                ],
                'lote' => ['folio' => 'LT-VITC-001', 'proveedor' => 'MediSur', 'caducidad_meses' => 22],
                'inventario' => ['centro' => 80, 'norte' => 60],
            ],
            [
                'codigo_barras' => '7501000100070',
                'nombre_producto' => 'Diclofenaco gel 1%',
                'descripcion' => 'Gel tópico antiinflamatorio. Tubo de 60g.',
                'categoria' => 'Analgésicos',
                'presentacion_principal' => 'caja',
                'precio_base' => 89.00,
                'es_controlado' => false,
                'precios' => [
                    ['presentacion' => 'caja', 'precio' => 89.00, 'unidades' => 1],
                ],
                'lote' => ['folio' => 'LT-DIC-001', 'proveedor' => 'Farmacias Rivera', 'caducidad_meses' => 16],
                'inventario' => ['centro' => 25, 'norte' => 0],
            ],
            [
                'codigo_barras' => '7501000100087',
                'nombre_producto' => 'Suero oral electrolitos',
                'descripcion' => 'Solución de rehidratación oral sabor manzana. Caja con 6 sobres.',
                'categoria' => 'Gastrointestinales',
                'presentacion_principal' => 'caja',
                'precio_base' => 64.00,
                'es_controlado' => false,
                'es_activo' => false,
                'precios' => [
                    ['presentacion' => 'caja', 'precio' => 64.00, 'unidades' => 6],
                    ['presentacion' => 'blister', 'precio' => 22.00, 'unidades' => 2],
                ],
                'lote' => ['folio' => 'LT-SUERO-001', 'proveedor' => 'Dist. del Centro', 'caducidad_meses' => 10],
                'inventario' => ['centro' => 12, 'norte' => 12],
            ],
        ];

        $mapPresentacion = ['caja' => $caja->id, 'blister' => $blister->id];

        foreach ($productos as $data) {
            DB::transaction(function () use ($data, $cat, $prov, $mapPresentacion, $sucCentro, $sucNorte): void {
                $idPrincipal = $mapPresentacion[$data['presentacion_principal']];

                $producto = Producto::updateOrCreate(
                    ['codigo_barras' => $data['codigo_barras']],
                    [
                        'nombre_producto' => $data['nombre_producto'],
                        'descripcion' => $data['descripcion'],
                        'precio' => $data['precio_base'],
                        'id_presentacion' => $idPrincipal,
                        'id_categoria' => $cat($data['categoria']),
                        'es_controlado' => $data['es_controlado'],
                        'es_activo' => $data['es_activo'] ?? true,
                        'stock' => 0,
                    ]
                );

                foreach ($data['precios'] as $precio) {
                    ProductoPresentacion::updateOrCreate(
                        [
                            'id_presentacion' => $mapPresentacion[$precio['presentacion']],
                            'producto' => $producto->id,
                        ],
                        [
                            'precio_presentacion' => $precio['precio'],
                            'unidades' => $precio['unidades'],
                        ]
                    );
                }

                $lote = Lote::updateOrCreate(
                    ['folio' => $data['lote']['folio']],
                    [
                        'id_producto' => $producto->id,
                        'id_presentacion' => $idPrincipal,
                        'id_proveedor' => $prov($data['lote']['proveedor']),
                        'stock_lote' => $data['inventario']['centro'] + $data['inventario']['norte'],
                        'entregado_en' => now(),
                        'fecha_caducidad' => now()->addMonths($data['lote']['caducidad_meses']),
                        'fecha_de_caducidad' => now()->addMonths($data['lote']['caducidad_meses']),
                    ]
                );

                Inventario::updateOrCreate(
                    ['id_sucursal' => $sucCentro->id, 'id_lote' => $lote->id],
                    ['stock' => $data['inventario']['centro']]
                );
                Inventario::updateOrCreate(
                    ['id_sucursal' => $sucNorte->id, 'id_lote' => $lote->id],
                    ['stock' => $data['inventario']['norte']]
                );

                Inventario::reflejarStockGlobal($producto->id);
            });
        }
    }
}
