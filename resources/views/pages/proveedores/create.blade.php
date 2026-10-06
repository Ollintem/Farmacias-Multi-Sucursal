<x-layouts::app :title="__('Nuevo proveedor')">
    <div class="module-page">
        <div class="module-page-inner mx-auto w-full max-w-5xl">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <a href="{{ route('proveedores.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-emerald-700 transition hover:text-emerald-900 dark:text-emerald-300 dark:hover:text-emerald-200">← Volver a proveedores</a>
                    <p class="farma-kicker mt-6">Catálogo de suministro</p>
                    <h1 class="mt-2">Agregar proveedor</h1>
                    <p class="theme-subtle mt-2 max-w-2xl text-sm leading-6">Registra la información necesaria para surtir tus farmacias con este proveedor.</p>
                </div>
                <span class="stat-pill positive w-fit">Nuevo registro</span>
            </div>

            <div class="module-card p-5 sm:p-8">
                <form method="POST" action="{{ route('proveedores.store') }}" class="space-y-8">
                    @csrf

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
                                <input id="nombre_proveedor" name="nombre_proveedor" value="{{ old('nombre_proveedor') }}" maxlength="20" class="theme-input" placeholder="Ej. Farma Distribuidora" required>
                                @error('nombre_proveedor')<span class="mt-1 block text-sm text-red-500">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label for="unidad_entrega" class="mb-2 block text-sm font-semibold">Unidad de entrega</label>
                                <input id="unidad_entrega" name="unidad_entrega" value="{{ old('unidad_entrega') }}" maxlength="20" class="theme-input" placeholder="Ej. Caja, Pieza, Lote" required>
                                @error('unidad_entrega')<span class="mt-1 block text-sm text-red-500">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="direccion" class="mb-2 block text-sm font-semibold">Dirección completa</label>
                        <textarea id="direccion" name="direccion" rows="3" class="theme-input" placeholder="Calle, número, colonia y ciudad" required>{{ old('direccion') }}</textarea>
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
                                <input id="telefono" type="tel" name="telefono" value="{{ old('telefono') }}" maxlength="20" class="theme-input" placeholder="Ej. 555 123 4567" required>
                                @error('telefono')<span class="mt-1 block text-sm text-red-500">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label for="correo" class="mb-2 block text-sm font-semibold">Correo de contacto</label>
                                <input id="correo" type="email" name="correo" value="{{ old('correo') }}" maxlength="30" class="theme-input" placeholder="proveedor@empresa.com" required>
                                @error('correo')<span class="mt-1 block text-sm text-red-500">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:justify-end dark:border-slate-700">
                        <a href="{{ route('proveedores.index') }}" class="theme-button theme-button-secondary">Cancelar</a>
                        <button type="submit" class="theme-button theme-button-primary">Guardar proveedor <span class="ml-2">→</span></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts::app>
