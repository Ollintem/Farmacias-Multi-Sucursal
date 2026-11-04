<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Cierra las cajas cuyo turno ya venció la tolerancia de 10 minutos.
// En producción debe estar corriendo `php artisan schedule:work` o el cron
// de Laravel; el POS y la campana también lo verifican de forma perezosa.
Schedule::command('cajas:cerrar-vencidas')->everyMinute()->withoutOverlapping();
