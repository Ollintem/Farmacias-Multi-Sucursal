<?php
if (!function_exists('__d7a0b6ea30c97ed0e6ec50c1c4090003')):
function __d7a0b6ea30c97ed0e6ec50c1c4090003($__blaze, $__data = [], $__slots = [], $__bound = [], $__keys = [], $__this = null) {
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
extract(Flux::forwardedAttributes($attributes, [
    'tooltipPosition',
    'tooltipKbd',
    'tooltip',
]));
?>

<?php $tooltipPosition = $tooltipPosition ??= $attributes->pluck('tooltip:position'); ?>
<?php $tooltipKbd = $tooltipKbd ??= $attributes->pluck('tooltip:kbd'); ?>
<?php $tooltip = $tooltip ??= $attributes->pluck('tooltip'); ?>

<?php
$__defaults = [
    'tooltipPosition' => 'top',
    'tooltipKbd' => null,
    'tooltip' => null,
];
$tooltipPosition ??= $attributes['tooltip-position'] ?? $attributes['tooltipPosition'] ?? $__defaults['tooltipPosition']; unset($attributes['tooltipPosition'], $attributes['tooltip-position']);
$tooltipKbd ??= $attributes['tooltip-kbd'] ?? $attributes['tooltipKbd'] ?? $__defaults['tooltipKbd']; unset($attributes['tooltipKbd'], $attributes['tooltip-kbd']);
$tooltip ??= $attributes['tooltip'] ?? $__defaults['tooltip']; unset($attributes['tooltip']);
unset($__defaults);
?>

<?php if ($tooltip): ?>
    <?php if (!function_exists('__399c335e9507a8a0fee0660498b8bea7')) { $__blaze->compile('C:\Users\Spirit of Fire\Documents\Reportes_servicio\residencias\proyecto\programacion\Farmacias-Multi-Sucursal\vendor\livewire\flux\src/../stubs/resources/views/flux/tooltip/index.blade.php', $__blaze->compiledPath.'/399c335e9507a8a0fee0660498b8bea7.php'); require $__blaze->compiledPath.'/399c335e9507a8a0fee0660498b8bea7.php'; } ?>
<?php if (isset($__slots399c335e9507a8a0fee0660498b8bea7)) { $__slotsStack399c335e9507a8a0fee0660498b8bea7[] = $__slots399c335e9507a8a0fee0660498b8bea7; } ?>
<?php if (isset($__attrs399c335e9507a8a0fee0660498b8bea7)) { $__attrsStack399c335e9507a8a0fee0660498b8bea7[] = $__attrs399c335e9507a8a0fee0660498b8bea7; } ?>
<?php $__attrs399c335e9507a8a0fee0660498b8bea7 = ['class' => 'inline-flex','content' => $tooltip,'position' => $tooltipPosition,'kbd' => $tooltipKbd]; ?>
<?php $__slots399c335e9507a8a0fee0660498b8bea7 = []; ?>
<?php $__blaze->pushData($__attrs399c335e9507a8a0fee0660498b8bea7); ?>
<?php ob_start(); ?>
        <?php echo e($slot); ?>

    <?php $__slots399c335e9507a8a0fee0660498b8bea7['slot'] = new \Illuminate\View\ComponentSlot($__blaze->processPassthroughContent('trim', trim(ob_get_clean())), []); ?>
<?php $__blaze->pushSlots($__slots399c335e9507a8a0fee0660498b8bea7); ?>
<?php __399c335e9507a8a0fee0660498b8bea7($__blaze, $__attrs399c335e9507a8a0fee0660498b8bea7, $__slots399c335e9507a8a0fee0660498b8bea7, ['content', 'position', 'kbd'], [], $__this ?? (isset($this) ? $this : null)); ?>
<?php if (! empty($__slotsStack399c335e9507a8a0fee0660498b8bea7)) { $__slots399c335e9507a8a0fee0660498b8bea7 = array_pop($__slotsStack399c335e9507a8a0fee0660498b8bea7); } ?>
<?php if (! empty($__attrsStack399c335e9507a8a0fee0660498b8bea7)) { $__attrs399c335e9507a8a0fee0660498b8bea7 = array_pop($__attrsStack399c335e9507a8a0fee0660498b8bea7); } ?>
<?php $__blaze->popData(); ?>
<?php else: ?>
    <?php echo e($slot); ?>

<?php endif; ?>
<?php
echo $__blaze->processPassthroughContent('ltrim', ltrim(ob_get_clean()));
} endif; ?><?php /**PATH C:\Users\Spirit of Fire\Documents\Reportes_servicio\residencias\proyecto\programacion\Farmacias-Multi-Sucursal\vendor\livewire\flux\src/../stubs/resources/views/flux/with-tooltip.blade.php ENDPATH**/ ?>