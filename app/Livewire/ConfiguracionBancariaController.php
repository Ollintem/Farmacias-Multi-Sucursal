<?php

namespace App\Livewire;

use App\Models\ConfiguracionBancaria;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Configuracion bancaria')]
class ConfiguracionBancariaController extends Component
{
    public string $banco = '';

    public string $clabe = '';

    public string $beneficiario = '';

    public string $numeroCuenta = '';

    public string $correoContacto = '';

    public bool $guardado = false;

    public function mount(): void
    {
        $config = ConfiguracionBancaria::obtener();

        $this->banco = $config->banco;
        $this->clabe = $config->clabe;
        $this->beneficiario = $config->beneficiario;
        $this->numeroCuenta = $config->numero_cuenta;
        $this->correoContacto = $config->correo_contacto;
    }

    public function guardar(): void
    {
        $this->validate([
            'banco' => 'required|string|max:100',
            'clabe' => 'required|string|max:20',
            'beneficiario' => 'required|string|max:150',
            'numeroCuenta' => 'required|string|max:30',
        ], [
            'banco.required' => 'El nombre del banco es obligatorio.',
            'clabe.required' => 'La CLABE interbancaria es obligatoria.',
            'beneficiario.required' => 'El nombre del beneficiario es obligatorio.',
            'numeroCuenta.required' => 'El numero de cuenta es obligatorio.',
        ]);

        $config = ConfiguracionBancaria::obtener();

        $config->update([
            'banco' => trim($this->banco),
            'clabe' => trim($this->clabe),
            'beneficiario' => trim($this->beneficiario),
            'numero_cuenta' => trim($this->numeroCuenta),
            'correo_contacto' => trim($this->correoContacto),
        ]);

        $this->guardado = true;

        $this->dispatch('configuracion-actualizada');
    }

    public function render()
    {
        return view('livewire.configuracion-bancaria');
    }
}
