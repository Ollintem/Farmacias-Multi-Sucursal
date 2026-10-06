@php
    $esEdicion = filled($sucursal);
@endphp

<x-layouts::app :title="$esEdicion ? __('Editar sucursal') : __('Nueva sucursal')">
    <div class="module-page">
        <div class="module-page-inner mx-auto w-full max-w-5xl">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <a href="{{ route('sucursales.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-emerald-700 transition hover:text-emerald-900 dark:text-emerald-300 dark:hover:text-emerald-200">← Volver a sucursales</a>
                    <p class="farma-kicker mt-6">Red de farmacias</p>
                    <h1 class="mt-2">{{ $esEdicion ? 'Editar sucursal' : 'Agregar sucursal' }}</h1>
                    <p class="theme-subtle mt-2 max-w-2xl text-sm leading-6">{{ $esEdicion ? 'Actualiza la información operativa y de contacto de esta farmacia.' : 'Registra la información necesaria para operar esta farmacia.' }}</p>
                </div>
                <span class="stat-pill {{ $esEdicion ? 'info' : 'positive' }} w-fit">{{ $esEdicion ? 'Edición de registro' : 'Nuevo registro' }}</span>
            </div>

            <div class="module-card p-5 sm:p-8">
                <form method="POST" action="{{ $esEdicion ? route('sucursales.update', $sucursal) : route('sucursales.store') }}" class="space-y-8">
                    @csrf
                    @if ($esEdicion)
                        @method('PUT')
                    @endif

                    <div>
                        <div class="mb-4">
                            <div>
                                <h2 class="text-sm font-bold">Identidad de la sucursal</h2>
                                <p class="theme-subtle text-xs">Cómo se identificará este punto de atención.</p>
                            </div>
                        </div>
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="nombre_sucursal" class="mb-2 block text-sm font-semibold">Nombre de la sucursal</label>
                                <input id="nombre_sucursal" name="nombre_sucursal" value="{{ old('nombre_sucursal', $sucursal?->nombre_sucursal) }}" class="theme-input" placeholder="Ej. Sucursal Poniente" required>
                                @error('nombre_sucursal')<span class="mt-1 block text-sm text-red-500">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label for="responsable" class="mb-2 block text-sm font-semibold">Responsable</label>
                                <input id="responsable" name="responsable" value="{{ old('responsable', $sucursal?->responsable) }}" class="theme-input" placeholder="Nombre del encargado">
                                @error('responsable')<span class="mt-1 block text-sm text-red-500">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="direccion" class="mb-2 block text-sm font-semibold">Dirección completa</label>
                        <input id="direccion" name="direccion" value="{{ old('direccion', $sucursal?->direccion) }}" class="theme-input" placeholder="Calle, número, colonia y ciudad" required>
                        @error('direccion')<span class="mt-1 block text-sm text-red-500">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <div class="mb-4">
                            <div>
                                <h2 class="text-sm font-bold">Contacto operativo</h2>
                                <p class="theme-subtle text-xs">Canales para localizar rápidamente a la sucursal.</p>
                            </div>
                        </div>
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="telefono" class="mb-2 block text-sm font-semibold">Teléfono de contacto</label>
                                <input id="telefono" type="tel" name="telefono" value="{{ old('telefono', $sucursal?->telefono) }}" class="theme-input" placeholder="Ej. 555 123 4567">
                                @error('telefono')<span class="mt-1 block text-sm text-red-500">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label for="correo_contacto" class="mb-2 block text-sm font-semibold">Correo de contacto</label>
                                <input id="correo_contacto" type="email" name="correo_contacto" value="{{ old('correo_contacto', $sucursal?->correo_contacto) }}" class="theme-input" placeholder="sucursal@farmacia.com">
                                @error('correo_contacto')<span class="mt-1 block text-sm text-red-500">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="mb-4">
                            <div>
                                <h2 class="text-sm font-bold">Horario operativo</h2>
                                <p class="theme-subtle text-xs">Define cuándo se encuentra disponible para atención.</p>
                            </div>
                        </div>
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="hora_apertura" class="mb-2 block text-sm font-semibold">Hora de apertura</label>
                                <input id="hora_apertura" type="time" name="hora_apertura" value="{{ old('hora_apertura', $sucursal?->hora_apertura ? substr($sucursal->hora_apertura, 0, 5) : '08:00') }}" class="theme-input" required>
                                @error('hora_apertura')<span class="mt-1 block text-sm text-red-500">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label for="hora_cierre" class="mb-2 block text-sm font-semibold">Hora de cierre</label>
                                <input id="hora_cierre" type="time" name="hora_cierre" value="{{ old('hora_cierre', $sucursal?->hora_cierre ? substr($sucursal->hora_cierre, 0, 5) : '20:00') }}" class="theme-input" required>
                                @error('hora_cierre')<span class="mt-1 block text-sm text-red-500">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>

                    <label class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4 dark:border-emerald-500/30 dark:bg-emerald-500/10">
                        <input type="checkbox" name="es_activa" value="1" @checked(old('es_activa', $sucursal?->es_activa ?? true))>
                        <span>
                            <span class="block text-sm font-bold text-emerald-900 dark:text-emerald-100">Sucursal activa</span>
                            <span class="block text-xs text-emerald-800/75 dark:text-emerald-200/75">Permite identificarla como disponible para la operación.</span>
                        </span>
                    </label>

                    <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:justify-end dark:border-slate-700">
                        <a href="{{ route('sucursales.index') }}" class="theme-button theme-button-secondary">Cancelar</a>
                        <button type="submit" class="theme-button theme-button-primary">{{ $esEdicion ? 'Guardar cambios' : 'Guardar sucursal' }} <span class="ml-2">→</span></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts::app>
