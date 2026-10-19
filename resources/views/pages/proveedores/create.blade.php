<x-layouts::app :title="__('Nuevo proveedor')">
    <div class="module-page noti-page">
        <div class="module-page-inner noti-full noti-formal">
            {{-- Encabezado homologado: hero verde --}}
            <section class="module-card noti-hero" aria-label="Encabezado de proveedor">
                <div class="noti-hero-fondo" aria-hidden="true"></div>
                <div class="noti-encabezado-texto noti-hero-texto">
                    <p class="noti-hero-kicker"><span class="noti-hero-dot"></span> Catálogo de suministro · Nuevo registro</p>
                    <h1 class="mt-2">Agregar proveedor</h1>
                </div>
            </section>

            <section class="module-card noti-listado" aria-label="Datos del proveedor">
                <header class="noti-listado-cabecera noti-listado-cabecera-empresa">
                    <div>
                        <h2>Datos del proveedor</h2>
                        <p class="theme-subtle text-sm">Identidad, dirección y contacto para surtir las farmacias</p>
                    </div>
                </header>
                <div class="p-5 sm:p-8">
                <form method="POST" action="{{ route('proveedores.store') }}" class="space-y-8" enctype="multipart/form-data">
                    @csrf

                    <div>
                        <div class="mb-4">
                            <div>
                                <h2 class="text-sm font-bold">Foto de perfil</h2>
                                <p class="theme-subtle text-xs">Opcional. Si no se sube, se muestra el avatar con la inicial.</p>
                            </div>
                        </div>
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                            <img id="foto-vista-previa" src="" alt="Vista previa de la foto" class="hidden h-20 w-20 rounded-2xl border border-slate-200 object-cover dark:border-slate-700">
                            <div class="flex-1">
                                <input id="foto" type="file" name="foto" accept="image/jpeg,image/png,image/webp" class="theme-input" aria-label="Foto del proveedor" onchange="document.getElementById('foto-vista-previa').src = this.files[0] ? URL.createObjectURL(this.files[0]) : ''; document.getElementById('foto-vista-previa').classList.toggle('hidden', !this.files[0]);">
                                <p class="theme-subtle mt-1 text-xs">JPG, PNG o WebP de hasta 2 MB.</p>
                                @error('foto')<span class="mt-1 block text-sm text-red-500">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="mb-4">
                            <div>
                                <h2 class="text-sm font-bold">Identidad del proveedor</h2>
                                <p class="theme-subtle text-xs">Cómo se identificará este proveedor en el sistema.</p>
                            </div>
                        </div>
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="nombre_proveedor" class="mb-2 block text-sm font-semibold">Nombre del proveedor</label>
                                <input id="nombre_proveedor" name="nombre_proveedor" value="{{ old('nombre_proveedor') }}" maxlength="120" class="theme-input" placeholder="Ej. Farma Distribuidora" required>
                                @error('nombre_proveedor')<span class="mt-1 block text-sm text-red-500">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label for="unidad_entrega" class="mb-2 block text-sm font-semibold">Unidad de entrega</label>
                                <input id="unidad_entrega" name="unidad_entrega" value="{{ old('unidad_entrega') }}" maxlength="60" class="theme-input" placeholder="Ej. Caja, Pieza, Lote" required>
                                @error('unidad_entrega')<span class="mt-1 block text-sm text-red-500">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label for="rfc" class="mb-2 block text-sm font-semibold">RFC <span class="font-normal opacity-60">(opcional)</span></label>
                                <input id="rfc" name="rfc" value="{{ old('rfc') }}" maxlength="13" class="theme-input uppercase" placeholder="Ej. FAD120315KT1">
                                @error('rfc')<span class="mt-1 block text-sm text-red-500">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label for="nombre_contacto" class="mb-2 block text-sm font-semibold">Nombre del contacto <span class="font-normal opacity-60">(opcional)</span></label>
                                <input id="nombre_contacto" name="nombre_contacto" value="{{ old('nombre_contacto') }}" maxlength="120" class="theme-input" placeholder="Ej. María González">
                                @error('nombre_contacto')<span class="mt-1 block text-sm text-red-500">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="direccion" class="mb-2 block text-sm font-semibold">Dirección completa</label>
                        <textarea id="direccion" name="direccion" rows="3" maxlength="500" class="theme-input" placeholder="Calle, número, colonia y ciudad" required>{{ old('direccion') }}</textarea>
                        @error('direccion')<span class="mt-1 block text-sm text-red-500">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <div class="mb-4">
                            <div>
                                <h2 class="text-sm font-bold">Contacto del proveedor</h2>
                                <p class="theme-subtle text-xs">Canales para localizar rápidamente al proveedor.</p>
                            </div>
                        </div>
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="telefono" class="mb-2 block text-sm font-semibold">Teléfono de contacto</label>
                                <input id="telefono" type="tel" name="telefono" value="{{ old('telefono') }}" maxlength="30" class="theme-input" placeholder="Ej. 555 123 4567" required>
                                @error('telefono')<span class="mt-1 block text-sm text-red-500">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label for="correo" class="mb-2 block text-sm font-semibold">Correo de contacto</label>
                                <input id="correo" type="email" name="correo" value="{{ old('correo') }}" maxlength="120" class="theme-input" placeholder="proveedor@empresa.com" required>
                                @error('correo')<span class="mt-1 block text-sm text-red-500">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>

                    <label class="flex cursor-pointer items-center gap-3">
                        <input type="hidden" name="es_activo" value="0">
                        <input type="checkbox" name="es_activo" value="1" @checked(old('es_activo', true))>
                        <span>
                            <span class="block text-sm font-bold">Proveedor activo</span>
                            <span class="theme-subtle block text-xs">Los inactivos se conservan en el directorio pero se marcan en revisión.</span>
                        </span>
                    </label>

                    <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:justify-end dark:border-slate-700">
                        <a href="{{ route('proveedores.index') }}" class="theme-button theme-button-secondary">Cancelar</a>
                        <button type="submit" class="theme-button theme-button-primary">Guardar proveedor <span class="ml-2">→</span></button>
                    </div>
                </form>
                </div>
            </section>
        </div>
    </div>
</x-layouts::app>
