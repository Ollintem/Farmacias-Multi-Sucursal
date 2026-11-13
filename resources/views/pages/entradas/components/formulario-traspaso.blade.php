<form action="{{ route('traspasos.store') }}" method="POST">
    @csrf

    <div class="grid gap-5 md:grid-cols-2">
        <div id="picker-sucursal" data-base-url="{{ route('traspasos.create') }}" data-actual="{{ $valorPicker }}" data-param="{{ $paramQuery }}" data-input="{{ $campoPicker }}">
            <label class="mb-2 block text-sm font-medium">Sucursal {{ $fijaEsDestino ? 'origen' : 'destino' }}</label>
            <x-option-pick :name="$campoPicker" :label="$fijaEsDestino ? 'Sucursal origen' : 'Sucursal destino'" :placeholder="$fijaEsDestino ? 'Selecciona el origen' : 'Selecciona el destino'" :options="$opcionesSucursal->pluck('nombre_sucursal', 'id')" :value="$valorPicker" />
            @if($errors->has($campoPicker))
                <span class="mt-1 block text-sm text-red-500">{{ $errors->first($campoPicker) }}</span>
            @endif
        </div>

        @include('pages.entradas.components.campo-sucursal-fija', [
            'etiqueta' => 'Sucursal ' . ($fijaEsDestino ? 'destino' : 'origen'),
            'campo' => $campoFijo,
            'valor' => $fija?->id,
            'nombreSucursal' => $fija?->nombre_sucursal ?? 'Sin sucursal',
            'ayuda' => ($fijaEsDestino ? 'El destino es tu sucursal activa' : 'El origen es tu sucursal activa') . ' y el traspaso nace en estado Pendiente.',
        ])

        <div class="md:col-span-2">
            <label class="mb-2 block text-sm font-medium">Mensaje para la sucursal destino</label>
            <textarea name="mensaje" rows="3" maxlength="1000" placeholder="Ej. Urgente: necesitamos 20 uds. para cubrir la venta del fin de semana." class="theme-input">{{ old('mensaje') }}</textarea>
            @error('mensaje')
                <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
            @enderror
        </div>
    </div>

    @include('pages.entradas.components.lotes-traspaso')

    @include('pages.entradas.components.acciones-formulario', [
        'cancelRoute' => route('alertas.index', ['sucursal' => $sucursalLink, 'filtro' => 'traspasos']),
        'submitLabel' => 'Enviar solicitud',
    ])
</form>
