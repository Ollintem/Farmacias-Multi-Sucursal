<?php if (isset($component)) { $__componentOriginal81a506f898233b9e7d58286e6bea3c18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal81a506f898233b9e7d58286e6bea3c18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'f4ac99e09542ff494432bc959d4fee61::app','data' => ['title' => __($tituloModulo ?? 'Módulo')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts::app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__($tituloModulo ?? 'Módulo'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <div class="flex flex-col gap-6 p-6">
        <div>
            <p class="text-sm uppercase tracking-[0.25em] text-emerald-500">FarmaERP</p>
            <h1 class="mt-2 text-3xl font-bold"><?php echo e($tituloModulo ?? 'Módulo'); ?></h1>
            <p class="mt-2 text-sm text-slate-500">Este módulo está habilitado en el catálogo y su pantalla definitiva está en construcción.</p>
        </div>

        <div class="rounded-2xl border border-dashed border-slate-300 bg-white/60 px-6 py-12 text-center">
            <p class="text-sm text-slate-500">Contenido de <span class="font-semibold"><?php echo e($tituloModulo ?? 'este módulo'); ?></span> próximamente.</p>
            <a href="<?php echo e(route('dashboard')); ?>" class="mt-4 inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Volver al dashboard</a>
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
<?php /**PATH C:\Users\Spirit of Fire\Documents\Reportes_servicio\residencias\proyecto\programacion\Farmacias-Multi-Sucursal\resources\views/pages/modulos/placeholder.blade.php ENDPATH**/ ?>