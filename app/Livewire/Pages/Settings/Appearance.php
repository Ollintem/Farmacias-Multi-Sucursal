<?php

namespace App\Livewire\Pages\Settings;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Ajustes de apariencia')]
class Appearance extends Component
{
    public function render()
    {
        return view('livewire.pages.settings.appearance');
    }
}
