<x-layouts::app :title="__('Editar proveedor: ' . $proveedor->nombre_proveedor)">
    <div class="min-h-full bg-[#f3f8f6] px-4 py-5 text-slate-900 dark:bg-[#0b1322] dark:text-white sm:px-6 lg:px-8 lg:py-7">
        <div class="mx-auto flex max-w-5xl flex-col gap-6">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <a href="{{ route('proveedores.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-emerald-700 transition hover:text-emerald-900 dark:text-emerald-300 dark:hover:text-emerald-200">← Volver a proveedores</a>
                    <div class="mt-6 flex items-center gap-3">
                        <span class="grid h-10 w-10 place-items-center rounded-2xl bg-emerald-600 text-sm font-black text-white">Rx</span>
                        <p class="text-xs font-bold uppercase tracking-[0.28em] text-emerald-700 dark:text-emerald-300">Catálogo de suministro</p>
                    </div>
                    <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-950 dark:text-white">Editar proveedor: {{ $proveedor->nombre_proveedor }}</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">Actualiza la información de contacto y entrega de este proveedor.</p>
                </div>
                <span class="w-fit rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">Edición de registro</span>
            </div>

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

            <div class="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-[0_14px_35px_rgba(15,23,42,0.06)] dark:border-slate-700 dark:bg-slate-900/75 sm:p-8">
                <form method="POST" action="{{ route('proveedores.update', $proveedor) }}" class="space-y-8">
                    @csrf
                    @method('PUT')

                    <div>
                        <div class="mb-4 flex items-center gap-3">
                            <span class="grid h-8 w-8 place-items-center rounded-lg bg-sky-100 text-xs font-black text-sky-700 dark:bg-sky-500/15 dark:text-sky-300">01</span>
                            <div>
                                <h2 class="text-sm font-bold text-slate-900 dark:text-white">Identidad del proveedor</h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Cómo se identificará este proveedor en el sistema.</p>
                            </div>
                        </div>
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="nombre_proveedor" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Nombre del proveedor</label>
                                <input id="nombre_proveedor" name="nombre_proveedor" value="{{ old('nombre_proveedor', $proveedor->nombre_proveedor) }}" maxlength="20" class="theme-input dark:border-slate-600 dark:bg-slate-950/50 dark:text-white dark:placeholder:text-slate-500" placeholder="Ej. Farma Distribuidora" required>
                                @error('nombre_proveedor')<span class="mt-1 block text-sm text-red-500">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label for="unidad_entrega" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Unidad de entrega</label>
                                <input id="unidad_entrega" name="unidad_entrega" value="{{ old('unidad_entrega', $proveedor->unidad_entrega) }}" maxlength="20" class="theme-input dark:border-slate-600 dark:bg-slate-950/50 dark:text-white dark:placeholder:text-slate-500" placeholder="Ej. Caja, Pieza, Lote" required>
                                @error('unidad_entrega')<span class="mt-1 block text-sm text-red-500">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="direccion" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Dirección completa</label>
                        <textarea id="direccion" name="direccion" rows="3" class="theme-input dark:border-slate-600 dark:bg-slate-950/50 dark:text-white dark:placeholder:text-slate-500" placeholder="Calle, número, colonia y ciudad" required>{{ old('direccion', $proveedor->direccion) }}</textarea>
                        @error('direccion')<span class="mt-1 block text-sm text-red-500">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <div class="mb-4 flex items-center gap-3">
                            <span class="grid h-8 w-8 place-items-center rounded-lg bg-emerald-100 text-xs font-black text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">02</span>
                            <div>
                                <h2 class="text-sm font-bold text-slate-900 dark:text-white">Contacto del proveedor</h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Canales para localizar rápidamente al proveedor.</p>
                            </div>
                        </div>
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="telefono" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Teléfono de contacto</label>
                                <input id="telefono" type="tel" name="telefono" value="{{ old('telefono', $proveedor->telefono) }}" maxlength="20" class="theme-input dark:border-slate-600 dark:bg-slate-950/50 dark:text-white dark:placeholder:text-slate-500" placeholder="Ej. 555 123 4567" required>
                                @error('telefono')<span class="mt-1 block text-sm text-red-500">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label for="correo" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Correo de contacto</label>
                                <input id="correo" type="email" name="correo" value="{{ old('correo', $proveedor->correo) }}" maxlength="30" class="theme-input dark:border-slate-600 dark:bg-slate-950/50 dark:text-white dark:placeholder:text-slate-500" placeholder="proveedor@empresa.com" required>
                                @error('correo')<span class="mt-1 block text-sm text-red-500">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:justify-end dark:border-slate-700">
                        <a href="{{ route('proveedores.index') }}" class="theme-button theme-button-secondary">Cancelar</a>
                        <button type="submit" class="theme-button theme-button-primary">Guardar cambios <span class="ml-2">→</span></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts::app>
