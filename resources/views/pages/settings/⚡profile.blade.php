<?php

use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Ajustes de perfil')] class extends Component {
    public string $nombre = '';
    public string $apellido = '';
    public string $nombre_usuario = '';
    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user();

        $this->nombre = $user->nombre;
        $this->apellido = $user->apellido;
        $this->nombre_usuario = $user->nombre_usuario;
        $this->email = $user->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'nombre_usuario' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('usuarios', 'email')->ignore($user->id),
            ],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        Flux::toast(variant: 'success', text: 'Perfil actualizado.');
    }

}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading level="2" class="sr-only">Ajustes de perfil</flux:heading>

    <x-pages::settings.layout heading="Perfil" subheading="Actualiza la información de tu perfil">
        <form wire:submit="updateProfileInformation" class="w-full space-y-6">
            <flux:input wire:model="nombre" label="Nombre" type="text" required autofocus autocomplete="given-name" />

            <flux:input wire:model="apellido" label="Apellido" type="text" required autocomplete="family-name" />

            <flux:input wire:model="nombre_usuario" label="Nombre de usuario" type="text" required autocomplete="username" />

            <div>
                <flux:input wire:model="email" label="Correo electrónico" type="email" required autocomplete="email" />

            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full" data-test="update-profile-button">
                        Guardar
                    </flux:button>
                </div>

            </div>
        </form>

            <livewire:pages::settings.delete-user-form />
    </x-pages::settings.layout>
</section>
