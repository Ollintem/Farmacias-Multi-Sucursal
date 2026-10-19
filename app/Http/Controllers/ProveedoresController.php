<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProveedoresController extends Controller
{
    /**
     * Lista los proveedores ordenados por nombre.
     *
     * Salida: resources/views/pages/proveedores/index.blade.php.
     */
    public function index(): View
    {
        $proveedores = Proveedor::orderBy('nombre_proveedor')->get();
        $totalProveedores = $proveedores->count();
        $proveedoresConCorreo = $proveedores->filter(fn ($proveedor) => filled($proveedor->correo))->count();
        $proveedoresSinTelefono = $proveedores->filter(fn ($proveedor) => blank($proveedor->telefono))->count();

        return view('pages.proveedores.index', compact(
            'proveedores',
            'totalProveedores',
            'proveedoresConCorreo',
            'proveedoresSinTelefono'
        ));
    }

    /**
     * Muestra el formulario de alta de proveedores.
     *
     * Salida: resources/views/pages/proveedores/create.blade.php.
     */
    public function create(): View
    {
        return view('pages.proveedores.create');
    }

    /**
     * Valida y registra un proveedor.
     *
     * Entrada: datos del formulario de alta (foto opcional).
     * Salida: redirección al listado de proveedores.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->reglas());

        if ($request->hasFile('foto')) {
            $data['foto_path'] = $request->file('foto')->store('proveedores', 'public');
        }

        Proveedor::create($data);

        return redirect()->route('proveedores.index')->with('success', 'Proveedor agregado correctamente.');
    }

    /**
     * Carga un proveedor existente para editar sus datos.
     *
     * Entrada: proveedor resuelto mediante route model binding.
     * Salida: resources/views/pages/proveedores/edit.blade.php.
     */
    public function edit(Proveedor $proveedor): View
    {
        return view('pages.proveedores.edit', compact('proveedor'));
    }

    /**
     * Valida y actualiza un proveedor existente.
     *
     * Entrada: datos del formulario y proveedor resuelto por la ruta.
     * Si llega foto nueva, reemplaza la anterior y borra el archivo viejo.
     * Salida: redirección al listado de proveedores.
     */
    public function update(Request $request, Proveedor $proveedor): RedirectResponse
    {
        $data = $request->validate($this->reglas($proveedor->id));

        if ($request->hasFile('foto')) {
            if (filled($proveedor->foto_path)) {
                Storage::disk('public')->delete($proveedor->foto_path);
            }

            $data['foto_path'] = $request->file('foto')->store('proveedores', 'public');
        }

        $proveedor->update($data);

        return redirect()->route('proveedores.index')->with('success', 'Proveedor actualizado correctamente.');
    }

    /**
     * Elimina un proveedor sin pedidos asociados (los pedidos son historial
     * y nunca se eliminan en cascada desde aquí). También borra su foto.
     *
     * Entrada: proveedor resuelto mediante route model binding.
     * Salida: redirección al listado con mensaje de éxito o de bloqueo.
     */
    public function destroy(Proveedor $proveedor): RedirectResponse
    {
        if ($proveedor->pedidos()->exists()) {
            return back()->with('error', "No se puede eliminar «{$proveedor->nombre_proveedor}»: tiene pedidos registrados. El historial de pedidos nunca se elimina.");
        }

        if (filled($proveedor->foto_path)) {
            Storage::disk('public')->delete($proveedor->foto_path);
        }

        $proveedor->delete();

        return back()->with('success', "«{$proveedor->nombre_proveedor}» se eliminó del directorio.");
    }

    /**
     * Reglas compartidas entre el alta y la edición de proveedores.
     *
     * Entrada: id a ignorar en el correo único (null en el alta).
     * Salida: arreglo de reglas de validación.
     *
     * @return array<string, array<int, mixed>>
     */
    private function reglas(?int $ignorarId = null): array
    {
        return [
            'nombre_proveedor' => ['required', 'string', 'max:120'],
            'direccion' => ['required', 'string', 'max:500'],
            'unidad_entrega' => ['required', 'string', 'max:60'],
            'telefono' => ['required', 'string', 'max:30'],
            'correo' => ['required', 'email', 'max:120', 'unique:proveedores,correo'.($ignorarId !== null ? ','.$ignorarId : '')],
            'rfc' => ['nullable', 'string', 'max:13', 'regex:/^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/i'],
            'nombre_contacto' => ['nullable', 'string', 'max:120'],
            'es_activo' => ['sometimes', 'boolean'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
