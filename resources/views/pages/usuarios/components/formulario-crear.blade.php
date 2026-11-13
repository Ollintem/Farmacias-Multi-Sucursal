<form action="{{ route('usuarios.store') }}" method="POST">
    @csrf

    <div class="module-table">
        <div class="p-5">
            <div
                x-data="permisosNuevoUsuario()"
                x-init="init()"
                @option-pick-changed="sincronizarRol($event)"
            >
                <div class="grid gap-5 md:grid-cols-2">
                    @include('pages.usuarios.components.campo-texto', [
                        'id' => 'nombre',
                        'name' => 'nombre',
                        'label' => 'Nombre',
                        'value' => old('nombre'),
                        'maxlength' => 100,
                        'autocomplete' => 'given-name',
                        'required' => true,
                    ])

                    @include('pages.usuarios.components.campo-texto', [
                        'id' => 'apellido',
                        'name' => 'apellido',
                        'label' => 'Apellido',
                        'value' => old('apellido'),
                        'maxlength' => 100,
                        'autocomplete' => 'family-name',
                        'required' => true,
                    ])

                    @include('pages.usuarios.components.campo-texto', [
                        'id' => 'email',
                        'name' => 'email',
                        'label' => 'Correo electrónico',
                        'type' => 'email',
                        'value' => old('email'),
                        'maxlength' => 255,
                        'autocomplete' => 'email',
                        'required' => true,
                    ])

                    @include('pages.usuarios.components.campo-rol', [
                        'roles' => $roles,
                        'value' => old('id_rol', ''),
                        'id' => 'id_rol',
                        'conPermisos' => true,
                    ])

                    @include('pages.usuarios.components.campo-sucursal-select', [
                        'sucursales' => $sucursales,
                        'value' => $selectedSucursalId ?? '',
                    ])

                    @include('pages.usuarios.components.toggle-password', [
                        'name' => 'password',
                        'label' => 'Contraseña',
                        'help' => 'Usa al menos 8 caracteres.',
                        'required' => true,
                    ])

                    @include('pages.usuarios.components.toggle-password', [
                        'name' => 'password_confirmation',
                        'label' => 'Confirmar contraseña',
                        'help' => 'Escríbela exactamente igual.',
                        'required' => true,
                    ])
                </div>

                @include('pages.usuarios.components.modal-permisos', ['modulos' => $modulos])
            </div>

            @include('pages.usuarios.components.campo-activo', ['checked' => old('es_activo', true)])
        </div>
    </div>

    @include('pages.usuarios.components.acciones-formulario', [
        'cancelRoute' => route('usuarios.index'),
        'submitLabel' => 'Guardar usuario',
    ])
</form>
