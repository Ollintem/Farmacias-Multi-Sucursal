<?php if (isset($component)) { $__componentOriginal81a506f898233b9e7d58286e6bea3c18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81a506f898233b9e7d58286e6bea3c18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'f4ac99e09542ff494432bc959d4fee61::app','data' => ['title' => __('Inventario')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts::app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Inventario'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <div id="theme-shell" class="theme-light">
        <div class="theme-shell-inner">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-emerald-500">Catálogo</p>
                    <h1 class="mt-2 text-3xl font-bold">Inventario farmacéutico</h1>
                </div>

                <div class="flex items-center gap-3">
                    <a href="<?php echo e(route('inventario.create', ['sucursal' => $selectedSucursal?->id])); ?>" class="theme-button theme-button-primary">+ Agregar producto nuevo</a>
                </div>
            </div>

            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                <div class="compact-stat">
                    <div class="flex items-center justify-between">
                        <span class="theme-subtle text-sm">Productos</span>
                        <span class="stat-pill positive"><?php echo e($productos->count()); ?></span>
                    </div>
                    <p class="mt-4 text-2xl font-bold"><?php echo e($productos->count()); ?></p>
                    <p class="mt-1 text-xs theme-subtle">Disponibles en la vista</p>
                </div>

                <div class="compact-stat">
                    <div class="flex items-center justify-between">
                        <span class="theme-subtle text-sm">Stock crítico</span>
                        <span class="stat-pill warning"><?php echo e($stockCritico); ?></span>
                    </div>
                    <p class="mt-4 text-2xl font-bold"><?php echo e($stockCritico); ?></p>
                    <p class="mt-1 text-xs theme-subtle">Productos con stock bajo</p>
                </div>

                <div class="compact-stat">
                    <div class="flex items-center justify-between">
                        <span class="theme-subtle text-sm">Unidades</span>
                        <span class="stat-pill info">Total</span>
                    </div>
                    <p class="mt-4 text-2xl font-bold"><?php echo e($stockTotal); ?></p>
                    <p class="mt-1 text-xs theme-subtle">Existencias actuales</p>
                </div>

                <div class="compact-stat">
                    <div class="flex items-center justify-between">
                        <span class="theme-subtle text-sm">Valor total</span>
                        <span class="stat-pill positive">MXN</span>
                    </div>
                    <p class="mt-4 text-2xl font-bold">$<?php echo e(number_format($valorTotal, 2)); ?></p>
                    <p class="mt-1 text-xs theme-subtle">Inventario visible</p>
                </div>
            </div>

            <div class="theme-card">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="theme-subtle text-xs uppercase tracking-[0.25em]">Sucursal activa</p>
                        <h2 class="mt-2 text-2xl font-bold"><?php echo e($selectedSucursal?->nombre_sucursal ?? 'Sin sucursal'); ?></h2>
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        <form method="GET" action="<?php echo e(route('inventario.index')); ?>" class="flex flex-col gap-2 sm:flex-row sm:items-center">
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
                            <input type="search" name="buscar" value="<?php echo e(old('buscar', $busqueda)); ?>" placeholder="Buscar código o nombre" class="theme-input w-full sm:w-64" aria-label="Buscar producto">
                            <button type="submit" class="theme-button theme-button-secondary whitespace-nowrap">Buscar</button>
                        </form>
                    </div>
                </div>

                <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white/80">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="bg-slate-50 text-slate-600">
                                <tr>
                                    <th class="px-4 py-3 font-semibold">Código</th>
                                    <th class="px-4 py-3 font-semibold">Producto</th>
                                    <th class="px-4 py-3 font-semibold">Descripción</th>
                                    <th class="px-4 py-3 font-semibold">Stock</th>
                                    <th class="px-4 py-3 font-semibold">Precio</th>
                                    <th class="px-4 py-3 font-semibold">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $productos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $producto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <tr class="border-t border-slate-200">
                                        <td class="px-4 py-3 font-medium text-slate-800"><?php echo e($producto->codigo_barras); ?></td>
                                        <td class="px-4 py-3 font-medium text-slate-800"><?php echo e($producto->nombre_producto); ?></td>
                                        <td class="px-4 py-3 text-slate-600"><?php echo e($producto->descripcion ?: 'Sin descripción'); ?></td>
                                        <td class="px-4 py-3 text-slate-700"><?php echo e($producto->stock); ?> uds.</td>
                                        <td class="px-4 py-3 text-slate-700">$<?php echo e(number_format($producto->precio, 2)); ?></td>
                                        <td class="px-4 py-3">
                                            <?php ($estado = $producto->stock <= 15 ? 'Bajo' : 'Disponible'); ?>
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold
                                                <?php if($estado === 'Disponible'): ?> bg-emerald-100 text-emerald-700
                                                <?php else: ?> bg-amber-100 text-amber-700
                                                <?php endif; ?>">
                                                <?php echo e($estado); ?>

                                            </span>
                                        </td>
                                    </tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    <tr>
                                        <td colspan="6" class="px-4 py-10 text-center text-slate-500">
                                            No se encontraron productos para esta sucursal o búsqueda.
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
<?php /**PATH C:\Users\Spirit of Fire\Documents\Reportes_servicio\residencias\proyecto\programacion\Farmacias-Multi-Sucursal\resources\views/pages/inventario/index.blade.php ENDPATH**/ ?>