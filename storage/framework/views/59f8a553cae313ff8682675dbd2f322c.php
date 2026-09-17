<?php if (isset($component)) { $__componentOriginal81a506f898233b9e7d58286e6bea3c18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81a506f898233b9e7d58286e6bea3c18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'f4ac99e09542ff494432bc959d4fee61::app','data' => ['title' => __('Lotes y caducidades')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts::app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Lotes y caducidades'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <div id="theme-shell" class="theme-light">
        <div class="theme-shell-inner">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-emerald-500">Trazabilidad</p>
                    <h1 class="mt-2 text-3xl font-bold">Lotes y caducidades</h1>
                </div>

                <div class="flex items-center gap-3">
                    <a href="<?php echo e(route('lotes.create', ['sucursal' => $selectedSucursal?->id])); ?>" class="theme-button theme-button-primary">+ Registrar nuevo lote</a>
                </div>
            </div>

            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                <div class="compact-stat">
                    <div class="flex items-center justify-between">
                        <span class="theme-subtle text-sm">Total de lotes</span>
                        <span class="stat-pill info"><?php echo e($totalLotes); ?></span>
                    </div>
                    <p class="mt-4 text-2xl font-bold"><?php echo e($totalLotes); ?></p>
                    <p class="mt-1 text-xs theme-subtle">Registros activos</p>
                </div>

                <div class="compact-stat">
                    <div class="flex items-center justify-between">
                        <span class="theme-subtle text-sm">Vigentes</span>
                        <span class="stat-pill positive">OK</span>
                    </div>
                    <p class="mt-4 text-2xl font-bold"><?php echo e($vigentes); ?></p>
                    <p class="mt-1 text-xs theme-subtle">Sin riesgo de caducidad</p>
                </div>

                <div class="compact-stat">
                    <div class="flex items-center justify-between">
                        <span class="theme-subtle text-sm">Por caducar</span>
                        <span class="stat-pill warning"><?php echo e($porCaducar); ?></span>
                    </div>
                    <p class="mt-4 text-2xl font-bold"><?php echo e($porCaducar); ?></p>
                    <p class="mt-1 text-xs theme-subtle">Menos de 90 días</p>
                </div>

                <div class="compact-stat">
                    <div class="flex items-center justify-between">
                        <span class="theme-subtle text-sm">Caducados</span>
                        <span class="stat-pill danger"><?php echo e($caducados); ?></span>
                    </div>
                    <p class="mt-4 text-2xl font-bold"><?php echo e($caducados); ?></p>
                    <p class="mt-1 text-xs theme-subtle">Requieren atención</p>
                </div>
            </div>

            <div class="theme-card">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="theme-subtle text-xs uppercase tracking-[0.25em]">Sucursal activa</p>
                        <h2 class="mt-2 text-2xl font-bold"><?php echo e($selectedSucursal?->nombre_sucursal ?? 'Sin sucursal'); ?></h2>
                    </div>

                    <form method="GET" action="<?php echo e(route('lotes.index')); ?>" class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        <label class="flex items-center gap-2 text-sm font-medium">
                            <span class="theme-subtle">Sucursal:</span>
                            <select name="sucursal" class="branch-select" aria-label="Seleccionar sucursal" onchange="this.form.submit()">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $sucursales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sucursal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <option value="<?php echo e($sucursal->id); ?>" <?php echo e($selectedSucursal && $selectedSucursal->id === $sucursal->id ? 'selected' : ''); ?>>
                                        <?php echo e($sucursal->nombre_sucursal); ?>

                                    </option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                        </label>

                        <input type="search" name="buscar" value="<?php echo e(old('buscar', $busqueda)); ?>" placeholder="Buscar folio, proveedor o producto" class="theme-input w-full sm:w-72" aria-label="Buscar lote">
                        <button type="submit" class="theme-button theme-button-secondary whitespace-nowrap">Buscar</button>
                    </form>
                </div>

                <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white/80">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="bg-slate-50 text-slate-600">
                                <tr>
                                    <th class="px-4 py-3 font-semibold">Lote</th>
                                    <th class="px-4 py-3 font-semibold">Producto</th>
                                    <th class="px-4 py-3 font-semibold">Proveedor</th>
                                    <th class="px-4 py-3 font-semibold">Sucursal</th>
                                    <th class="px-4 py-3 font-semibold">Cantidad</th>
                                    <th class="px-4 py-3 font-semibold">Entrada</th>
                                    <th class="px-4 py-3 font-semibold">Caducidad</th>
                                    <th class="px-4 py-3 font-semibold">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $lotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <tr class="border-t border-slate-200">
                                        <td class="px-4 py-3 font-medium text-slate-800"><?php echo e($lote['folio']); ?></td>
                                        <td class="px-4 py-3">
                                            <div class="font-medium text-slate-800"><?php echo e($lote['producto']); ?></div>
                                        </td>
                                        <td class="px-4 py-3 text-slate-600"><?php echo e($lote['marca']); ?></td>
                                        <td class="px-4 py-3 text-slate-700"><?php echo e($lote['sucursal']); ?></td>
                                        <td class="px-4 py-3 text-slate-700"><?php echo e($lote['cantidad']); ?> uds.</td>
                                        <td class="px-4 py-3 text-slate-600"><?php echo e($lote['fecha_entrada']); ?></td>
                                        <td class="px-4 py-3 text-slate-600"><?php echo e($lote['fecha_caducidad']); ?></td>
                                        <td class="px-4 py-3">
                                            <span class="status-badge <?php echo e($lote['estado_class']); ?>"><?php echo e($lote['estado']); ?></span>
                                        </td>
                                    </tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    <tr>
                                        <td colspan="8" class="px-4 py-10 text-center text-slate-500">
                                            No se encontraron lotes para la sucursal o búsqueda actual.
                                        </td>
                                    </tr>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal81a506f898233b9e7d58286e6bea3c18)): ?>
<?php $attributes = $__attributesOriginal81a506f898233b9e7d58286e6bea3c18; ?>
<?php unset($__attributesOriginal81a506f898233b9e7d58286e6bea3c18); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal81a506f898233b9e7d58286e6bea3c18)): ?>
<?php $component = $__componentOriginal81a506f898233b9e7d58286e6bea3c18; ?>
<?php unset($__componentOriginal81a506f898233b9e7d58286e6bea3c18); ?>
<?php endif; ?>
<?php /**PATH C:\Users\Spirit of Fire\Documents\Reportes_servicio\residencias\proyecto\programacion\Farmacias-Multi-Sucursal\resources\views/pages/lotes/index.blade.php ENDPATH**/ ?>