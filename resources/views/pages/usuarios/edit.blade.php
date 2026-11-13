<x-layouts::app :title="__('Editar usuario: ' . $usuario->nombre . ' ' . $usuario->apellido)">
    <div class="module-page noti-page">
        <div class="module-page-inner noti-full noti-formal">
            @include('pages.usuarios.components.encabezado-acciones', ['usuario' => $usuario])
            @include('pages.usuarios.components.alertas')

            <section class="module-card noti-listado" aria-label="Datos del usuario">
                @include('pages.usuarios.components.cabecera-datos', [
                    'titulo' => 'Datos del usuario',
                    'subtitulo' => 'Actualice la información general de la cuenta',
                ])

                <div class="p-5">
                    @include('pages.usuarios.components.formulario-editar', [
                        'usuario' => $usuario,
                        'roles' => $roles,
                        'sucursales' => $sucursales,
                    ])
                </div>
            </section>
        </div>
    </div>
</x-layouts::app>
