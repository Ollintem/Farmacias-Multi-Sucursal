<x-layouts::app :title="__('Nuevo usuario')">
    <div class="module-page noti-page">
        <div class="module-page-inner noti-full noti-formal">
            @include('pages.usuarios.components.alertas')

            <section class="module-card noti-listado" aria-label="Datos del usuario">
                @include('pages.usuarios.components.cabecera-datos', [
                    'titulo' => 'Datos del usuario',
                    'subtitulo' => 'Completa la información para registrar un nuevo usuario',
                ])

                <div class="p-5">
                    @include('pages.usuarios.components.formulario-crear', [
                        'roles' => $roles,
                        'sucursales' => $sucursales,
                        'modulos' => $modulos,
                        'selectedSucursalId' => $selectedSucursalId ?? null,
                    ])
                </div>
            </section>
        </div>
    </div>

    @include('pages.usuarios.components.script-permisos', [
        'permisosPorRol' => $permisosPorRol,
        'roles' => $roles,
        'modulos' => $modulos,
    ])
</x-layouts::app>
