<x-layouts::app :title="__(($modo ?? 'editar') === 'ver' ? 'Ver rol' : 'Editar rol')">
    @php
        $soloLectura = ($modo ?? 'editar') === 'ver';
    @endphp
    <div id="theme-shell" class="theme-light">
        <div class="theme-shell-inner">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-emerald-500">Usuarios y roles</p>
                    <h1 class="mt-2 text-3xl font-bold">{{ $soloLectura ? 'Ver rol' : 'Editar rol' }}: {{ $rol->tipo_rol }}</h1>
                    @if($soloLectura)
                        <p class="mt-2 text-sm text-slate-500">Consulta de solo lectura de los datos del rol.</p>
                    @endif
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('roles.index') }}" class="theme-button theme-button-secondary">Volver</a>
                    @if($soloLectura && $rol->tipo_rol !== 'SuperAdmin')
                        <a href="{{ route('roles.edit', $rol) }}" class="theme-button theme-button-primary">Editar rol</a>
                    @endif
                </div>
            </div>

            @if(session('success'))
                <div class="rounded-xl border border-emerald-500/25 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-600">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('roles.update', $rol) }}" method="POST" class="theme-card">
                @csrf
                @method('PUT')

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium">Nombre del rol</label>
                        <input name="tipo_rol" value="{{ old('tipo_rol', $rol->tipo_rol) }}" class="theme-input" maxlength="50" required @disabled($soloLectura)>
                        @error('tipo_rol')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Descripción</label>
                        <input name="descripcion" value="{{ old('descripcion', $rol->descripcion) }}" class="theme-input" maxlength="255" @disabled($soloLectura)>
                        @error('descripcion')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex items-center gap-2 text-sm text-slate-500">
                    <span>Usuarios con este rol: <strong class="text-slate-700">{{ $rol->usuarios_count ?? $rol->usuarios()->count() }}</strong></span>
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <a href="{{ route('roles.index') }}" class="theme-button theme-button-secondary">Cancelar</a>
                    @if($soloLectura)
                        @if($rol->tipo_rol !== 'SuperAdmin')
                            <a href="{{ route('roles.edit', $rol) }}" class="theme-button theme-button-primary">Editar rol</a>
                        @endif
                    @else
                        <button type="submit" class="theme-button theme-button-primary">Guardar cambios</button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
