<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SucursalesController extends Controller
{
    public function create(): View
    {
        return view('sucursales.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre_sucursal' => ['required', 'string', 'max:150', 'unique:sucursales,nombre_sucursal'],
            'direccion' => ['required', 'string', 'max:255'],
            'hora_apertura' => ['required', 'date_format:H:i'],
            'hora_cierre' => ['required', 'date_format:H:i', 'after:hora_apertura'],
        ]);

        Sucursal::create($data);

        return redirect()->route('dashboard')->with('success', 'Sucursal agregada correctamente.');
    }
}
