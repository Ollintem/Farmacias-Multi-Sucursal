<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    /**
     * Registra una categoría nueva y la retorna como JSON.
     *
     * Entrada: nombre de la categoría.
     * Salida: JSON con id y nombre de la categoría creada.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:60', 'unique:categorias,nombre'],
        ]);

        $categoria = Categoria::create($data);

        return response()->json($categoria->only(['id', 'nombre']));
    }
}
