<?php

namespace App\Livewire\Pages\Settings;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Ajustes de apariencia')]
class Appearance extends Component
{
    public string $modo = 'claro';

    public function mount(): void
    {
        $this->modo = session('theme_modo', 'claro');
    }

    public function cambiarTema(string $nuevoModo): void
    {
        $this->modo = $nuevoModo;
        session(['theme_modo' => $nuevoModo]);
        $this->dispatch('aplicar-tema', modo: $nuevoModo);
    }

    public function render()
    {
        return view('livewire.pages.settings.appearance');
    }
}
