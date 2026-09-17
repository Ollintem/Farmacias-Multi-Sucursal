<?php
if (!function_exists('__399c335e9507a8a0fee0660498b8bea7')):
function __399c335e9507a8a0fee0660498b8bea7($__blaze, $__data = [], $__slots = [], $__bound = [], $__keys = [], $__this = null) {
$__env = $__blaze->env;
$__slots['slot'] ??= new \Illuminate\View\ComponentSlot('');
if (($__data['attributes'] ?? null) instanceof \Illuminate\View\ComponentAttributeBag) { $__data = $__data + $__data['attributes']->all(); unset($__data['attributes']); }
extract($__slots, EXTR_SKIP); unset($__slots);
extract($__data, EXTR_SKIP);
$attributes = \Livewire\Blaze\Runtime\BlazeAttributeBag::make($__data, $__bound, $__keys);
unset($__data, $__bound, $__keys);
ob_start();
?>


<?php
$__defaults = [
    'interactive' => null,
    'position' => 'top',
    'align' => 'center',
    'content' => null,
    'kbd' => null,
    'toggleable' => null,
];
$interactive ??= $attributes['interactive'] ?? $__defaults['interactive']; unset($attributes['interactive']);
$position ??= $attributes['position'] ?? $__defaults['position']; unset($attributes['position']);
$align ??= $attributes['align'] ?? $__defaults['align']; unset($attributes['align']);
$content ??= $attributes['content'] ?? $__defaults['content']; unset($attributes['content']);
$kbd ??= $attributes['kbd'] ?? $__defaults['kbd']; unset($attributes['kbd']);
$toggleable ??= $attributes['toggleable'] ?? $__defaults['toggleable']; unset($attributes['toggleable']);
unset($__defaults);
?>

<?php
// Support adding the .self modifier to the wire:model directive...
if (($wireModel = $attributes->wire('model')) && $wireModel->directive && ! $wireModel->hasModifier('self')) {
    unset($attributes[$wireModel->directive]);

    $wireModel->directive .= '.self';

    $attributes = $attributes->merge([$wireModel->directive => $wireModel->value]);
}
?>

<?php if ($toggleable): ?>
    <ui-dropdown position="<?php echo e($position); ?> <?php echo e($align); ?>" <?php echo e($attributes); ?> data-flux-tooltip>
        <?php echo e($slot); ?>


        <?php if ($content !== null): ?>
            <?php if (!function_exists('__84f31c3d6eff60488ffa80e09a26769b')) { $__blaze->compile('C:\Users\Spirit of Fire\Documents\Reportes_servicio\residencias\proyecto\programacion\Farmacias-Multi-Sucursal\vendor\livewire\flux\src/../stubs/resources/views/flux/tooltip/content.blade.php', $__blaze->compiledPath.'/84f31c3d6eff60488ffa80e09a26769b.php'); require $__blaze->compiledPath.'/84f31c3d6eff60488ffa80e09a26769b.php'; } ?>
<?php if (isset($__slots84f31c3d6eff60488ffa80e09a26769b)) { $__slotsStack84f31c3d6eff60488ffa80e09a26769b[] = $__slots84f31c3d6eff60488ffa80e09a26769b; } ?>
<?php if (isset($__attrs84f31c3d6eff60488ffa80e09a26769b)) { $__attrsStack84f31c3d6eff60488ffa80e09a26769b[] = $__attrs84f31c3d6eff60488ffa80e09a26769b; } ?>
<?php $__attrs84f31c3d6eff60488ffa80e09a26769b = ['kbd' => $kbd]; ?>
<?php $__slots84f31c3d6eff60488ffa80e09a26769b = []; ?>
<?php $__blaze->pushData($__attrs84f31c3d6eff60488ffa80e09a26769b); ?>
<?php ob_start(); ?><?php echo e($content); ?><?php $__slots84f31c3d6eff60488ffa80e09a26769b['slot'] = new \Illuminate\View\ComponentSlot($__blaze->processPassthroughContent('trim', trim(ob_get_clean())), []); ?>
<?php $__blaze->pushSlots($__slots84f31c3d6eff60488ffa80e09a26769b); ?>
<?php __84f31c3d6eff60488ffa80e09a26769b($__blaze, $__attrs84f31c3d6eff60488ffa80e09a26769b, $__slots84f31c3d6eff60488ffa80e09a26769b, ['kbd'], [], $__this ?? (isset($this) ? $this : null)); ?>
<?php if (! empty($__slotsStack84f31c3d6eff60488ffa80e09a26769b)) { $__slots84f31c3d6eff60488ffa80e09a26769b = array_pop($__slotsStack84f31c3d6eff60488ffa80e09a26769b); } ?>
<?php if (! empty($__attrsStack84f31c3d6eff60488ffa80e09a26769b)) { $__attrs84f31c3d6eff60488ffa80e09a26769b = array_pop($__attrsStack84f31c3d6eff60488ffa80e09a26769b); } ?>
<?php $__blaze->popData(); ?>
        <?php endif; ?>
    </ui-dropdown>
<?php else: ?>
    <ui-tooltip position="<?php echo e($position); ?> <?php echo e($align); ?>" <?php echo e($attributes); ?> data-flux-tooltip <?php if($interactive): ?> interactive <?php endif; ?>>
        <?php echo e($slot); ?>


        <?php if ($content !== null): ?>
            <?php if (!function_exists('__84f31c3d6eff60488ffa80e09a26769b')) { $__blaze->compile('C:\Users\Spirit of Fire\Documents\Reportes_servicio\residencias\proyecto\programacion\Farmacias-Multi-Sucursal\vendor\livewire\flux\src/../stubs/resources/views/flux/tooltip/content.blade.php', $__blaze->compiledPath.'/84f31c3d6eff60488ffa80e09a26769b.php'); require $__blaze->compiledPath.'/84f31c3d6eff60488ffa80e09a26769b.php'; } ?>
<?php if (isset($__slots84f31c3d6eff60488ffa80e09a26769b)) { $__slotsStack84f31c3d6eff60488ffa80e09a26769b[] = $__slots84f31c3d6eff60488ffa80e09a26769b; } ?>
<?php if (isset($__attrs84f31c3d6eff60488ffa80e09a26769b)) { $__attrsStack84f31c3d6eff60488ffa80e09a26769b[] = $__attrs84f31c3d6eff60488ffa80e09a26769b; } ?>
<?php $__attrs84f31c3d6eff60488ffa80e09a26769b = ['kbd' => $kbd]; ?>
<?php $__slots84f31c3d6eff60488ffa80e09a26769b = []; ?>
<?php $__blaze->pushData($__attrs84f31c3d6eff60488ffa80e09a26769b); ?>
<?php ob_start(); ?><?php echo e($content); ?><?php $__slots84f31c3d6eff60488ffa80e09a26769b['slot'] = new \Illuminate\View\ComponentSlot($__blaze->processPassthroughContent('trim', trim(ob_get_clean())), []); ?>
<?php $__blaze->pushSlots($__slots84f31c3d6eff60488ffa80e09a26769b); ?>
<?php __84f31c3d6eff60488ffa80e09a26769b($__blaze, $__attrs84f31c3d6eff60488ffa80e09a26769b, $__slots84f31c3d6eff60488ffa80e09a26769b, ['kbd'], [], $__this ?? (isset($this) ? $this : null)); ?>
<?php if (! empty($__slotsStack84f31c3d6eff60488ffa80e09a26769b)) { $__slots84f31c3d6eff60488ffa80e09a26769b = array_pop($__slotsStack84f31c3d6eff60488ffa80e09a26769b); } ?>
<?php if (! empty($__attrsStack84f31c3d6eff60488ffa80e09a26769b)) { $__attrs84f31c3d6eff60488ffa80e09a26769b = array_pop($__attrsStack84f31c3d6eff60488ffa80e09a26769b); } ?>
<?php $__blaze->popData(); ?>
        <?php endif; ?>
    </ui-tooltip>
<?php endif; ?>
<?php
echo $__blaze->processPassthroughContent('ltrim', ltrim(ob_get_clean()));
} endif; ?><?php /**PATH C:\Users\Spirit of Fire\Documents\Reportes_servicio\residencias\proyecto\programacion\Farmacias-Multi-Sucursal\vendor\livewire\flux\src/../stubs/resources/views/flux/tooltip/index.blade.php ENDPATH**/ ?>