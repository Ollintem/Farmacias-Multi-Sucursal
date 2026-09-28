<?php

namespace App\Http\Controllers;

use App\Models\PresentacionProducto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PresentacionController extends Controller
{
    /**
     * Registra una presentación nueva y la retorna como JSON.
     *
     * Entrada: nombre de la presentación.
     * Salida: JSON con id y nombre de la presentación creada.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'presentacion' => ['required', 'string', 'max:20', 'unique:presentaciones,presentacion'],
        ]);

        $presentacion = PresentacionProducto::create([
            'presentacion' => $data['presentacion'],
            'descripcion' => '',
        ]);

        return response()->json($presentacion->only(['id', 'presentacion']));
    }
}
