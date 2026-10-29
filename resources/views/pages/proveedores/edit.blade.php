<x-layouts::app :title="__('Editar proveedor: ' . $proveedor->nombre_proveedor)">
    <div class="module-page noti-page">
        <div class="module-page-inner noti-full noti-formal">
            {{-- Encabezado limpio --}}
            <header class="mb-6" aria-label="Encabezado de proveedor">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Catálogo de suministro · Edición</p>
                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Editar proveedor: {{ $proveedor->nombre_proveedor }}</h1>
                </div>
            </header>

            @if (session('success'))
                <div
                    x-data="{ show: true }"
                    x-init="setTimeout(() => show = false, 2000)"
                    x-show="show"
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200"
                >
                    {{ session('success') }}
                </div>
            @endif

            <section class="module-card noti-listado" aria-label="Datos del proveedor">
                <header class="noti-listado-cabecera noti-listado-cabecera-empresa">
                    <div>
                        <h2>Datos del proveedor</h2>
                        <p class="theme-subtle text-sm">Actualice la información de contacto y entrega</p>
                    </div>
                </header>
                <div class="p-5 sm:p-8">
                <form method="POST" action="{{ route('proveedores.update', $proveedor) }}" class="space-y-8">
                    @csrf
                    @method('PUT')

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
                                <input id="nombre_proveedor" name="nombre_proveedor" value="{{ old('nombre_proveedor', $proveedor->nombre_proveedor) }}" maxlength="120" class="theme-input" placeholder="Ej. Farma Distribuidora" required>
                                @error('nombre_proveedor')<span class="mt-1 block text-sm text-red-500">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label for="unidad_entrega" class="mb-2 block text-sm font-semibold">Unidad de entrega</label>
                                <input id="unidad_entrega" name="unidad_entrega" value="{{ old('unidad_entrega', $proveedor->unidad_entrega) }}" maxlength="60" class="theme-input" placeholder="Ej. Caja, Pieza, Lote" required>
                                @error('unidad_entrega')<span class="mt-1 block text-sm text-red-500">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label for="rfc" class="mb-2 block text-sm font-semibold">RFC <span class="font-normal opacity-60">(opcional)</span></label>
                                <input id="rfc" name="rfc" value="{{ old('rfc', $proveedor->rfc) }}" maxlength="13" class="theme-input uppercase" placeholder="Ej. FAD120315KT1">
                                @error('rfc')<span class="mt-1 block text-sm text-red-500">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label for="nombre_contacto" class="mb-2 block text-sm font-semibold">Nombre del contacto <span class="font-normal opacity-60">(opcional)</span></label>
                                <input id="nombre_contacto" name="nombre_contacto" value="{{ old('nombre_contacto', $proveedor->nombre_contacto) }}" maxlength="120" class="theme-input" placeholder="Ej. María González">
                                @error('nombre_contacto')<span class="mt-1 block text-sm text-red-500">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="direccion" class="mb-2 block text-sm font-semibold">Dirección completa</label>
                        <textarea id="direccion" name="direccion" rows="3" maxlength="500" class="theme-input" placeholder="Calle, número, colonia y ciudad" required>{{ old('direccion', $proveedor->direccion) }}</textarea>
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
                                <input id="telefono" type="tel" name="telefono" value="{{ old('telefono', $proveedor->telefono) }}" maxlength="30" class="theme-input" placeholder="Ej. 555 123 4567" required>
                                @error('telefono')<span class="mt-1 block text-sm text-red-500">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label for="correo" class="mb-2 block text-sm font-semibold">Correo de contacto</label>
                                <input id="correo" type="email" name="correo" value="{{ old('correo', $proveedor->correo) }}" maxlength="120" class="theme-input" placeholder="proveedor@empresa.com" required>
                                @error('correo')<span class="mt-1 block text-sm text-red-500">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>

                    <label class="flex cursor-pointer items-center gap-3">
                        <input type="hidden" name="es_activo" value="0">
                        <input type="checkbox" name="es_activo" value="1" @checked(old('es_activo', $proveedor->es_activo))>
                        <span>
                            <span class="block text-sm font-bold">Proveedor activo</span>
                            <span class="theme-subtle block text-xs">Los inactivos se conservan en el directorio pero se marcan en revisión.</span>
                        </span>
                    </label>

                    <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:justify-end dark:border-slate-700">
                        <a href="{{ route('proveedores.index') }}" class="theme-button theme-button-secondary">Cancelar</a>
                        <button type="submit" class="theme-button theme-button-primary">Guardar cambios <span class="ml-2">→</span></button>
                    </div>
                </form>
                </div>
            </section>
        </div>
    </div>
</x-layouts::app>
