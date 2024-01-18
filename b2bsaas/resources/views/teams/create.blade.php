<?php

use Laravel\Jetstream\RedirectsActions;
use Laravel\Jetstream\InteractsWithBanner;

use Laravel\Jetstream\Contracts\CreatesTeams;
use function Livewire\Volt\{uses, computed, state};

uses([
    RedirectsActions::class,
    InteractsWithBanner::class,
]);

state([
    'state' => [],
]);

$user = computed(fn() => Auth::user());

$teamDatabases = computed(fn() => $this->user->teamDatabases);

$createTeam = function(CreatesTeams $creator) {
    $this->resetErrorBag();

    // if the state['team_database'] is empty or null, just unset it
    if (empty($this->state['team_database_uuid'])) {
        unset($this->state['team_database_uuid']);
    }

    $creator->create($this->user, $this->state);

    return $this->redirectPath($creator);
}; ?>

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Create Team') }}
        </h2>
    </x-slot>

    <div>
        <div class="py-10 mx-auto max-w-7xl sm:px-6 lg:px-8">
            @volt('teams.create-team-form')
            <div>
                <x-form-section submit="createTeam">
                    <x-slot name="title">
                        {{ __('Team Details') }}
                    </x-slot>
                    
                    <x-slot name="description">
                        {{ __('Create a new team to collaborate with others on projects.') }}
                    </x-slot>
                    
                    <x-slot name="form">
                        <div class="col-span-6">
                            <x-label value="{{ __('Team Owner') }}" />
                            
                            <div class="flex items-center mt-2">
                                <img class="object-cover w-12 h-12 rounded-full" src="{{ $this->user->profile_photo_url }}" alt="{{ $this->user->name }}">
                                
                                <div class="leading-tight ms-4">
                                    <div class="text-gray-900 dark:text-white">{{ $this->user->name }}</div>
                                    <div class="text-sm text-gray-700 dark:text-gray-300">{{ $this->user->email }}</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-span-6 sm:col-span-4">
                            <x-label for="name" value="{{ __('Database') }}" />
                            <x-select id="name" type="text" class="block w-full mt-1" wire:model="state.team_database_uuid" autofocus>
                                <option selected></option>
                                @foreach ($this->teamDatabases as $teamDatabase)
                                <option value="{{ $teamDatabase->uuid }}">{{ $teamDatabase->name }}</option>
                                @endforeach
                            </x-select>
                            <x-form-help-text for="name" class="mt-2">
                                {{ __('Leave blank to create a fresh database for your new team.') }}
                            </x-form-help-text>
                            <x-input-error for="name" class="mt-2" />
                        </div>
                        
                        <div class="col-span-6 sm:col-span-4">
                            <x-label for="name" value="{{ __('Team Name') }}" />
                            <x-input id="name" type="text" class="block w-full mt-1" wire:model="state.name" autofocus />
                            <x-input-error for="name" class="mt-2" />
                        </div>
                    </x-slot>
                    
                    <x-slot name="actions">
                        <x-button>
                            {{ __('Create') }}
                        </x-button>
                    </x-slot>
                </x-form-section>
                </div>
                @endvolt
            </div>
    </div>
</x-app-layout>
