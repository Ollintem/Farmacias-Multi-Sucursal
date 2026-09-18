<x-layouts::app :title="__('Nuevo rol')">
    <div id="theme-shell" class="theme-light">
        <div class="theme-shell-inner">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-emerald-500">Usuarios y roles</p>
                    <h1 class="mt-2 text-3xl font-bold">Registrar nuevo rol</h1>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('roles.index') }}" class="theme-button theme-button-secondary">Volver</a>
                </div>
            </div>

            <form action="{{ route('roles.store') }}" method="POST" class="theme-card">
                @csrf

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium">Nombre del rol</label>
                        <input name="tipo_rol" value="{{ old('tipo_rol') }}" class="theme-input" maxlength="50" required>
                        @error('tipo_rol')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Descripción</label>
                        <input name="descripcion" value="{{ old('descripcion') }}" class="theme-input" maxlength="255">
                        @error('descripcion')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <a href="{{ route('roles.index') }}" class="theme-button theme-button-secondary">Cancelar</a>
                    <button type="submit" class="theme-button theme-button-primary">Guardar rol</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
