<x-layouts::app :title="__('Nueva sucursal')">
    <div id="theme-shell" class="theme-light">
        <div class="theme-shell-inner">
            <div>
                <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-emerald-600">← Volver al dashboard</a>
                <p class="mt-6 text-sm uppercase tracking-[0.25em] text-emerald-500">Administración</p>
                <h1 class="mt-2 text-3xl font-bold">Agregar sucursal</h1>
                <p class="mt-2 text-slate-500">Registra una nueva farmacia para comenzar a consultar sus indicadores.</p>
            </div>

            <div class="theme-card max-w-3xl">
                <form method="POST" action="{{ route('sucursales.store') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="nombre_sucursal" class="mb-2 block text-sm font-medium">Nombre de la sucursal</label>
                        <input id="nombre_sucursal" name="nombre_sucursal" value="{{ old('nombre_sucursal') }}" class="theme-input" placeholder="Ej. Sucursal Poniente" required>
                        @error('nombre_sucursal')<span class="mt-1 block text-sm text-red-500">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <label for="direccion" class="mb-2 block text-sm font-medium">Dirección</label>
                        <input id="direccion" name="direccion" value="{{ old('direccion') }}" class="theme-input" placeholder="Calle, número y colonia" required>
                        @error('direccion')<span class="mt-1 block text-sm text-red-500">{{ $message }}</span>@enderror
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="hora_apertura" class="mb-2 block text-sm font-medium">Hora de apertura</label>
                            <input id="hora_apertura" type="time" name="hora_apertura" value="{{ old('hora_apertura', '08:00') }}" class="theme-input" required>
                            @error('hora_apertura')<span class="mt-1 block text-sm text-red-500">{{ $message }}</span>@enderror
                        </div>

                        <div>
                            <label for="hora_cierre" class="mb-2 block text-sm font-medium">Hora de cierre</label>
                            <input id="hora_cierre" type="time" name="hora_cierre" value="{{ old('hora_cierre', '20:00') }}" class="theme-input" required>
                            @error('hora_cierre')<span class="mt-1 block text-sm text-red-500">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-3">
                        <a href="{{ route('dashboard') }}" class="theme-button theme-button-secondary">Cancelar</a>
                        <button type="submit" class="theme-button theme-button-primary">Guardar sucursal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts::app>
