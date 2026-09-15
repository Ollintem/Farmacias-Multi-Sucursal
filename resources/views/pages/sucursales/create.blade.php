<x-layouts::app :title="__('Nueva sucursal')">
    <div id="theme-shell" class="theme-light">
        <style>
            #theme-shell { width: 100%; min-height: 100%; color: #0f172a; }
            .theme-light { background: linear-gradient(180deg, #f4f9f3 0%, #edf3ef 100%); }
            .theme-dark { background: linear-gradient(180deg, #111827 0%, #0b1220 100%); color: #f8fafc; }
            .theme-shell-inner { display: flex; flex-direction: column; gap: 1.5rem; width: 100%; padding: 1.5rem; }
            .theme-card { border-radius: 1.25rem; border: 1px solid #dfeae0; padding: 1.5rem; background: rgba(255, 255, 255, 0.9); }
            .theme-dark .theme-card { background: rgba(15, 23, 42, 0.72); border-color: rgba(148, 163, 184, 0.25); }
            .theme-input { width: 100%; border-radius: 0.85rem; border: 1px solid #d8e5d8; background: rgba(255, 255, 255, 0.85); padding: 0.7rem 0.9rem; color: #0f172a; outline: none; }
            .theme-dark .theme-input { border-color: rgba(148, 163, 184, 0.3); background: rgba(15, 23, 42, 0.8); color: #f8fafc; }
            .theme-button { display: inline-flex; align-items: center; justify-content: center; border-radius: 0.9rem; padding: 0.7rem 1rem; font-weight: 600; text-decoration: none; }
            .theme-button-primary { background: linear-gradient(135deg, #22c55e, #16a34a); color: #052e16; }
            .theme-button-secondary { border: 1px solid #d8e5d8; background: rgba(255, 255, 255, 0.8); color: #0f172a; }
            .theme-dark .theme-button-secondary { border-color: rgba(148, 163, 184, 0.3); background: rgba(15, 23, 42, 0.8); color: #f8fafc; }
        </style>

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
