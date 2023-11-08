<?php

use function Livewire\Volt\{state, mount, usesFileUploads};

state(
    [
        'team' => null,
        'state' => [],
    ]
);

mount(
    function ($team) {
        $this->team = $team;
    }
);
?>

<x-form-section submit="updateTeamDomain">
    <x-slot name="title">
        {{ __('Team Domain') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Team\'s domain.') }}
    </x-slot>

    <x-slot name="form">

        <!-- Company Name -->
        <div class="col-span-6 sm:col-span-4">
    
                <x-label for="domain" value="{{ __('Team Domain') }}" />
      
                <x-input id="domain" type="text" class="block w-full mt-1" wire:model.defer="state.domain"
                    :disabled="! Gate::check('updateTeamDomain', $team)" />
                
                <x-form-help-text>
                    {{ __('Please contact your administrator in order to to request a custom domain.') }}
                </x-form-help-text>

                <x-secondary-button-link class="py-2.5 mt-1" target="_blank" href="{{ $team->url }}">
                    <span>Visit</span>
                </x-secondary-button-link>

               <x-input-error for="domain" class="mt-2" />
        </div>
    </x-slot>

    @if (Gate::check('updateTeamDomain', $team))
    <x-slot name="actions">
        <x-action-message class="mr-3" on="saved">
            {{ __('Saved.') }}
        </x-action-message>

        <x-button :disabled="! Gate::check('updateTeamDomain', $team)">
            {{ __('Save') }}
        </x-button>
    </x-slot>
    @endif
</x-form-section>
