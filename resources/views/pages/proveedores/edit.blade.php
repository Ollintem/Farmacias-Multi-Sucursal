<x-layouts::app :title="__('Editar proveedor: ' . $proveedor->nombre_proveedor)">
    <div class="module-page noti-page">
        <div class="module-page-inner noti-full noti-formal">
            {{-- Encabezado homologado: hero verde --}}
            <section class="module-card noti-hero" aria-label="Encabezado de proveedor">
                <div class="noti-hero-fondo" aria-hidden="true"></div>
                <div class="noti-encabezado-texto noti-hero-texto">
                    <p class="noti-hero-kicker"><span class="noti-hero-dot"></span> Catálogo de suministro · Edición</p>
                    <h1 class="mt-2">Editar proveedor: {{ $proveedor->nombre_proveedor }}</h1>
                </div>
            </section>

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
                <form method="POST" action="{{ route('proveedores.update', $proveedor) }}" class="space-y-8" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div>
                        <div class="mb-4">
                            <div>
                                <h2 class="text-sm font-bold">Foto de perfil</h2>
                                <p class="theme-subtle text-xs">Opcional. Si no se sube, se muestra el avatar con la inicial.</p>
                            </div>
                        </div>
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start"
                            x-data="{
                                original: '{{ $proveedor->fotoUrl() }}',
                                actual: '{{ $proveedor->fotoUrl() }}',
                                previa: null,
                                previaUrl: null,
                                nombre: '',
                                elegir(evento) {
                                    const archivo = evento.target.files[0];
                                    if (! archivo) {
                                        return;
                                    }
                                    if (this.previaUrl) {
                                        URL.revokeObjectURL(this.previaUrl);
                                    }
                                    this.previaUrl = URL.createObjectURL(archivo);
                                    this.previa = this.previaUrl;
                                    this.nombre = archivo.name;
                                },
                                confirmar() {
                                    this.actual = this.previa;
                                    this.limpiarPrevia();
                                },
                                cancelar() {
                                    this.$refs.foto.value = '';
                                    this.limpiarPrevia();
                                },
                                limpiarPrevia() {
                                    if (this.previaUrl) {
                                        URL.revokeObjectURL(this.previaUrl);
                                    }
                                    this.previa = null;
                                    this.previaUrl = null;
                                    this.nombre = '';
                                }
                            }">
                            <div class="shrink-0">
                                <template x-if="actual">
                                    <img :src="actual" alt="Foto de {{ $proveedor->nombre_proveedor }}" class="h-20 w-20 rounded-2xl border border-slate-200 object-cover dark:border-slate-700">
                                </template>
                                <template x-if="! actual">
                                    <span class="grid h-20 w-20 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-[#0e9384] to-[#0c7569] text-2xl font-black text-white">{{ strtoupper(mb_substr($proveedor->nombre_proveedor ?? '?', 0, 1)) }}</span>
                                </template>
                                <button type="button" x-show="actual !== original && ! previa" x-cloak @click="actual = original; $refs.foto.value = '';" class="mt-2 w-fit rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-800 transition hover:bg-amber-200 dark:bg-amber-500/15 dark:text-amber-300 dark:hover:bg-amber-500/25" title="Volver a la foto anterior">Sin guardar · deshacer</button>
                            </div>
                            <div class="flex-1">
                                <input id="foto" x-ref="foto" type="file" name="foto" accept="image/jpeg,image/png,image/webp" class="theme-input" aria-label="Foto del proveedor" @change="elegir($event)">
                                <p class="theme-subtle mt-1 text-xs">JPG, PNG o WebP de hasta 2 MB. Reemplaza la foto actual.</p>
                                <div x-show="previa" x-cloak class="mt-3 flex flex-col gap-3 rounded-2xl border border-dashed border-slate-300 bg-slate-50/70 p-3 sm:flex-row sm:items-center dark:border-slate-700 dark:bg-slate-900/40">
                                    <img :src="previa" alt="Vista previa de la foto nueva" class="h-14 w-14 shrink-0 rounded-xl border border-slate-200 object-cover dark:border-slate-700">
                                    <p class="min-w-0 flex-1 truncate text-sm font-medium" x-text="nombre"></p>
                                    <div class="flex shrink-0 gap-2">
                                        <button type="button" @click="confirmar()" class="theme-button theme-button-primary !min-h-0 !px-4 !py-2 !text-xs">Confirmar foto</button>
                                        <button type="button" @click="cancelar()" class="theme-button theme-button-secondary !min-h-0 !px-4 !py-2 !text-xs">Cancelar</button>
                                    </div>
                                </div>
                                @error('foto')<span class="mt-1 block text-sm text-red-500">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>

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
