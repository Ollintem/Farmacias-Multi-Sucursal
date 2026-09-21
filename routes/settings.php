<?php

use App\Livewire\ConfiguracionBancariaController;
use App\Livewire\Pages\Settings\Appearance;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::livewire('settings/profile', 'pages::settings.profile')->name('profile.edit');

    Route::get('settings/transferencia', ConfiguracionBancariaController::class)->name('settings.transferencia');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('settings/appearance', Appearance::class)->name('appearance.edit');

    Route::livewire('settings/security', 'pages::settings.security')
        ->name('security.edit');
});
