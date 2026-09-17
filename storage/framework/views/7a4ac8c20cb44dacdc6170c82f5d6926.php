<?php if (isset($component)) { $__componentOriginal81a506f898233b9e7d58286e6bea3c18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81a506f898233b9e7d58286e6bea3c18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'f4ac99e09542ff494432bc959d4fee61::app','data' => ['title' => __('Dashboard')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts::app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Dashboard'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <div id="theme-shell" class="theme-light">
        <div class="theme-shell-inner">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-emerald-500">Panel general</p>
                    <h1 class="mt-2 text-3xl font-bold">Dashboard farmacéutico</h1>
                </div>

                <div class="flex items-center gap-3">
                    <a href="<?php echo e(route('usuarios.index')); ?>" class="theme-button theme-button-primary">Ver usuarios</a>
                    <a href="<?php echo e(route('usuarios.create')); ?>" class="theme-button theme-button-secondary">Nuevo usuario</a>
                </div>
            </div>

            <div class="theme-card">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="theme-subtle text-xs uppercase tracking-[0.25em]">Sucursal activa</p>
                        <h2 class="mt-2 text-2xl font-bold">Centro Histórico</h2>
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        <label class="flex items-center gap-2 text-sm font-medium">
                            <span class="theme-subtle">Ver:</span>
                            <select id="branch-select" class="branch-select" aria-label="Seleccionar sucursal">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $sucursales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sucursal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <option value="<?php echo e($sucursal->id); ?>" data-name="<?php echo e($sucursal->nombre_sucursal); ?>" <?php echo e($loop->first ? 'selected' : ''); ?>>
                                        <?php echo e($sucursal->nombre_sucursal); ?>

                                    </option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                        </label>
                        <a href="<?php echo e(route('sucursales.create')); ?>" class="theme-button theme-button-secondary whitespace-nowrap">+ Agregar sucursal</a>
                    </div>
                </div>

                <div class="mt-5 grid gap-3 md:grid-cols-3">
                    <div class="compact-stat">
                        <div class="flex items-center justify-between">
                            <span class="theme-subtle text-sm">Ventas</span>
                            <span class="stat-pill positive">+12%</span>
                        </div>
                        <p id="stat-ventas" class="mt-4 text-2xl font-bold">Sin datos</p>
                        <p class="mt-1 text-xs theme-subtle">Aún no disponible</p>
                    </div>

                    <div class="compact-stat">
                        <div class="flex items-center justify-between">
                            <span class="theme-subtle text-sm">Clientes</span>
                            <span class="stat-pill info">Pendiente</span>
                        </div>
                        <p id="stat-clientes" class="mt-4 text-2xl font-bold">Sin datos</p>
                        <p class="mt-1 text-xs theme-subtle">Aún no disponible</p>
                    </div>

                    <div class="compact-stat">
                        <div class="flex items-center justify-between">
                            <span class="theme-subtle text-sm">Stock</span>
                            <span class="stat-pill warning">Pendiente</span>
                        </div>
                        <p id="stat-stock" class="mt-4 text-2xl font-bold">Sin datos</p>
                        <p class="mt-1 text-xs theme-subtle">Aún no disponible</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 lg:grid-cols-[1.8fr_1fr]">
                <div class="space-y-4">
                    <div class="theme-card">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-lg font-semibold">Acceso rápido</h2>
                            <span class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2 py-1 text-xs font-medium text-emerald-600">Superadmin</span>
                        </div>

                        <div class="grid gap-4 md:grid-cols-3">
                            <a href="<?php echo e(route('usuarios.index')); ?>" class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 transition hover:border-emerald-400 hover:bg-emerald-100/80">
                                <p class="text-base font-semibold">Usuarios</p>
                                <p class="mt-1 text-sm text-slate-500">Gestión de personal</p>
                            </a>

                            <a href="#" class="rounded-2xl border border-sky-200 bg-sky-50 p-4 transition hover:border-sky-400 hover:bg-sky-100/80">
                                <p class="text-base font-semibold">Inventario</p>
                                <p class="mt-1 text-sm text-slate-500">Productos y stock</p>
                            </a>

                            <a href="#" class="rounded-2xl border border-violet-200 bg-violet-50 p-4 transition hover:border-violet-400 hover:bg-violet-100/80">
                                <p class="text-base font-semibold">Sucursales</p>
                                <p class="mt-1 text-sm text-slate-500">Control por tienda</p>
                            </a>
                        </div>
                    </div>

                    <div class="theme-card">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="theme-subtle text-sm">Resumen de operación</p>
                                <h3 class="mt-1 text-xl font-bold">Farmacia hoy</h3>
                            </div>
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500">Sin datos</span>
                        </div>

                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-sm text-slate-600">Ventas en línea</p>
                                <p class="mt-2 text-2xl font-bold text-slate-400">Sin datos</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-sm text-slate-600">Pedidos pendientes</p>
                                <p class="mt-2 text-2xl font-bold text-slate-400">Sin datos</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="theme-card">
                        <h2 class="text-lg font-semibold">Estado del sistema</h2>

                        <div class="mt-5 space-y-4">
                            <p class="rounded-xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm theme-subtle">
                                Aún no hay información del sistema para mostrar.
                            </p>
                        </div>
                    </div>

                    <div class="theme-card">
                        <h2 class="text-lg font-semibold">Actividad reciente</h2>
                        <ul class="mt-4 space-y-3">
                            <li class="rounded-xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm theme-subtle">
                                No hay actividad reciente.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <script>
            (() => {
                const branchSelect = document.getElementById('branch-select');
                const title = document.querySelector('.theme-card h2');

                const updateBranchStats = (value) => {
                    const option = [...(branchSelect?.options || [])].find((item) => item.value === value);
                    if (title) title.textContent = option?.dataset.name || 'Sucursal activa';
                };

                branchSelect?.addEventListener('change', (event) => updateBranchStats(event.target.value));
                updateBranchStats(branchSelect?.value || '');
            })();
        </script>
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
<?php /**PATH C:\Users\Spirit of Fire\Documents\Reportes_servicio\residencias\proyecto\programacion\Farmacias-Multi-Sucursal\resources\views/dashboard.blade.php ENDPATH**/ ?>