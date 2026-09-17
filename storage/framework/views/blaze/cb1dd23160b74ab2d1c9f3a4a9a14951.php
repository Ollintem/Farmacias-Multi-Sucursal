<?php
if (!function_exists('__cb1dd23160b74ab2d1c9f3a4a9a14951')):
function __cb1dd23160b74ab2d1c9f3a4a9a14951($__blaze, $__data = [], $__slots = [], $__bound = [], $__keys = [], $__this = null) {
$__env = $__blaze->env;
$__slots['slot'] ??= new \Illuminate\View\ComponentSlot('');
if (($__data['attributes'] ?? null) instanceof \Illuminate\View\ComponentAttributeBag) { $__data = $__data + $__data['attributes']->all(); unset($__data['attributes']); }
extract($__slots, EXTR_SKIP); unset($__slots);
extract($__data, EXTR_SKIP);
$attributes = \Livewire\Blaze\Runtime\BlazeAttributeBag::make($__data, $__bound, $__keys);
unset($__data, $__bound, $__keys);
ob_start();
?>


<ui-menu-radio-group <?php echo e($attributes); ?> data-flux-menu-radio-group>
    <?php echo e($slot); ?>

</ui-menu-radio-group>
<?php
echo $__blaze->processPassthroughContent('ltrim', ltrim(ob_get_clean()));
} endif; ?><?php /**PATH C:\Users\Spirit of Fire\Documents\Reportes_servicio\residencias\proyecto\programacion\Farmacias-Multi-Sucursal\vendor\livewire\flux\src/../stubs/resources/views/flux/menu/radio/group.blade.php ENDPATH**/ ?>