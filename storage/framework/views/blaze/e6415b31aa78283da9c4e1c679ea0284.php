<?php
if (!function_exists('__e6415b31aa78283da9c4e1c679ea0284')):
function __e6415b31aa78283da9c4e1c679ea0284($__blaze, $__data = [], $__slots = [], $__bound = [], $__keys = [], $__this = null) {
$__env = $__blaze->env;

if (($__data['attributes'] ?? null) instanceof \Illuminate\View\ComponentAttributeBag) { $__data = $__data + $__data['attributes']->all(); unset($__data['attributes']); }
extract($__slots, EXTR_SKIP); unset($__slots);
extract($__data, EXTR_SKIP);
$attributes = \Livewire\Blaze\Runtime\BlazeAttributeBag::make($__data, $__bound, $__keys);
unset($__data, $__bound, $__keys);
ob_start();
?>


<div <?php echo e($attributes->class('flex-1')); ?> data-flux-spacer></div>
<?php
echo $__blaze->processPassthroughContent('ltrim', ltrim(ob_get_clean()));
} endif; ?><?php /**PATH C:\Users\Spirit of Fire\Documents\Reportes_servicio\residencias\proyecto\programacion\Farmacias-Multi-Sucursal\vendor\livewire\flux\src/../stubs/resources/views/flux/spacer.blade.php ENDPATH**/ ?>