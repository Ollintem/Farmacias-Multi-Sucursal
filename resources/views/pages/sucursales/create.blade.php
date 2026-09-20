@php
    $esEdicion = filled($sucursal);
@endphp

<x-layouts::app :title="$esEdicion ? __('Editar sucursal') : __('Nueva sucursal')">
    <div class="min-h-full bg-[#f3f8f6] px-4 py-5 text-slate-900 dark:bg-[#0b1322] dark:text-white sm:px-6 lg:px-8 lg:py-7">
        <div class="mx-auto flex max-w-5xl flex-col gap-6">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <a href="{{ route('sucursales.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-emerald-700 transition hover:text-emerald-900 dark:text-emerald-300 dark:hover:text-emerald-200">← Volver a sucursales</a>
                    <div class="mt-6 flex items-center gap-3">
                        <span class="grid h-10 w-10 place-items-center rounded-2xl bg-emerald-600 text-sm font-black text-white">Rx</span>
                        <p class="text-xs font-bold uppercase tracking-[0.28em] text-emerald-700 dark:text-emerald-300">Red de farmacias</p>
                    </div>
                    <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-950 dark:text-white">{{ $esEdicion ? 'Editar sucursal' : 'Agregar sucursal' }}</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $esEdicion ? 'Actualiza la información operativa y de contacto de esta farmacia.' : 'Registra la información necesaria para operar esta farmacia.' }}</p>
                </div>
                <span class="w-fit rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">{{ $esEdicion ? 'Edición de registro' : 'Nuevo registro' }}</span>
            </div>

            <div class="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-[0_14px_35px_rgba(15,23,42,0.06)] dark:border-slate-700 dark:bg-slate-900/75 sm:p-8">
                <form method="POST" action="{{ $esEdicion ? route('sucursales.update', $sucursal) : route('sucursales.store') }}" class="space-y-8">
                    @csrf
                    @if ($esEdicion)
                        @method('PUT')
                    @endif

                    <div>
                        <div class="mb-4 flex items-center gap-3">
                            <span class="grid h-8 w-8 place-items-center rounded-lg bg-sky-100 text-xs font-black text-sky-700 dark:bg-sky-500/15 dark:text-sky-300">01</span>
                            <div>
                                <h2 class="text-sm font-bold text-slate-900 dark:text-white">Identidad de la sucursal</h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Cómo se identificará este punto de atención.</p>
                            </div>
                        </div>
                        <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="nombre_sucursal" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Nombre de la sucursal</label>
                            <input id="nombre_sucursal" name="nombre_sucursal" value="{{ old('nombre_sucursal', $sucursal?->nombre_sucursal) }}" class="theme-input dark:border-slate-600 dark:bg-slate-950/50 dark:text-white dark:placeholder:text-slate-500" placeholder="Ej. Sucursal Poniente" required>
                        </div>
                        <div>
                            <label for="responsable" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Responsable</label>
                            <input id="responsable" name="responsable" value="{{ old('responsable', $sucursal?->responsable) }}" class="theme-input dark:border-slate-600 dark:bg-slate-950/50 dark:text-white dark:placeholder:text-slate-500" placeholder="Nombre del encargado">
                        </div>
                        </div>
                    </div>
                    @error('nombre_sucursal')<span class="-mt-4 block text-sm text-red-500">{{ $message }}</span>@enderror
                    @error('responsable')<span class="-mt-4 block text-sm text-red-500">{{ $message }}</span>@enderror

                    <div>
                        <label for="direccion" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Dirección completa</label>
                        <input id="direccion" name="direccion" value="{{ old('direccion', $sucursal?->direccion) }}" class="theme-input dark:border-slate-600 dark:bg-slate-950/50 dark:text-white dark:placeholder:text-slate-500" placeholder="Calle, número, colonia y ciudad" required>
                        @error('direccion')<span class="mt-1 block text-sm text-red-500">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <div class="mb-4 flex items-center gap-3">
                            <span class="grid h-8 w-8 place-items-center rounded-lg bg-emerald-100 text-xs font-black text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">02</span>
                            <div>
                                <h2 class="text-sm font-bold text-slate-900 dark:text-white">Contacto operativo</h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Canales para localizar rápidamente a la sucursal.</p>
                            </div>
                        </div>
                        <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="telefono" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Teléfono de contacto</label>
                            <input id="telefono" type="tel" name="telefono" value="{{ old('telefono', $sucursal?->telefono) }}" class="theme-input dark:border-slate-600 dark:bg-slate-950/50 dark:text-white dark:placeholder:text-slate-500" placeholder="Ej. 555 123 4567">
                            @error('telefono')<span class="mt-1 block text-sm text-red-500">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label for="correo_contacto" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Correo de contacto</label>
                            <input id="correo_contacto" type="email" name="correo_contacto" value="{{ old('correo_contacto', $sucursal?->correo_contacto) }}" class="theme-input dark:border-slate-600 dark:bg-slate-950/50 dark:text-white dark:placeholder:text-slate-500" placeholder="sucursal@farmacia.com">
                            @error('correo_contacto')<span class="mt-1 block text-sm text-red-500">{{ $message }}</span>@enderror
                        </div>
                        </div>
                    </div>

                    <div>
                        <div class="mb-4 flex items-center gap-3">
                            <span class="grid h-8 w-8 place-items-center rounded-lg bg-amber-100 text-xs font-black text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">03</span>
                            <div>
                                <h2 class="text-sm font-bold text-slate-900 dark:text-white">Horario operativo</h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Define cuándo se encuentra disponible para atención.</p>
                            </div>
                        </div>
                        <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="hora_apertura" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Hora de apertura</label>
                            <input id="hora_apertura" type="time" name="hora_apertura" value="{{ old('hora_apertura', $sucursal?->hora_apertura ? substr($sucursal->hora_apertura, 0, 5) : '08:00') }}" class="theme-input dark:border-slate-600 dark:bg-slate-950/50 dark:text-white" required>
                            @error('hora_apertura')<span class="mt-1 block text-sm text-red-500">{{ $message }}</span>@enderror
                        </div>

                        <div>
                            <label for="hora_cierre" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Hora de cierre</label>
                            <input id="hora_cierre" type="time" name="hora_cierre" value="{{ old('hora_cierre', $sucursal?->hora_cierre ? substr($sucursal->hora_cierre, 0, 5) : '20:00') }}" class="theme-input dark:border-slate-600 dark:bg-slate-950/50 dark:text-white" required>
                            @error('hora_cierre')<span class="mt-1 block text-sm text-red-500">{{ $message }}</span>@enderror
                        </div>
                        </div>
                    </div>

                    <label class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4 dark:border-emerald-500/30 dark:bg-emerald-500/10">
                        <input type="checkbox" name="es_activa" value="1" class="h-4 w-4 rounded border-slate-300 text-emerald-600" @checked(old('es_activa', $sucursal?->es_activa ?? true))>
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
