<form action="{{ route('usuarios.update', $usuario) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="module-table !rounded-none !border-0 !shadow-none">
        <div class="p-5">
            <div class="mt-5 grid gap-5 md:grid-cols-2">
                @include('pages.usuarios.components.campo-texto', [
                    'name' => 'nombre',
                    'label' => 'Nombre',
                    'value' => old('nombre', $usuario->nombre),
                    'required' => true,
                ])

                @include('pages.usuarios.components.campo-texto', [
                    'name' => 'apellido',
                    'label' => 'Apellido',
                    'value' => old('apellido', $usuario->apellido),
                    'required' => true,
                ])

                @include('pages.usuarios.components.campo-texto', [
                    'name' => 'email',
                    'label' => 'Email',
                    'type' => 'email',
                    'value' => old('email', $usuario->email),
                    'required' => true,
                ])

                @include('pages.usuarios.components.campo-rol', [
                    'roles' => $roles,
                    'value' => $usuario->id_rol,
                ])

                @include('pages.usuarios.components.campo-sucursal-fija', [
                    'idSucursal' => $usuario->id_sucursal,
                    'nombreSucursal' => $sucursales->firstWhere('id', $usuario->id_sucursal)?->nombre_sucursal ?? 'Sin sucursal',
                ])

                @include('pages.usuarios.components.toggle-password', [
                    'name' => 'password',
                    'label' => 'Nueva contraseña',
                    'help' => 'Déjala en blanco para mantener la actual.',
                ])

                @include('pages.usuarios.components.toggle-password', [
                    'name' => 'password_confirmation',
                    'label' => 'Confirmar contraseña',
                ])
            </div>

            @include('pages.usuarios.components.campo-activo', ['checked' => old('es_activo', $usuario->es_activo)])
        </div>
    </div>

    @include('pages.usuarios.components.acciones-formulario', [
        'cancelRoute' => route('usuarios.index'),
        'submitLabel' => 'Guardar cambios',
    ])
</form>
