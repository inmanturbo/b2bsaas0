<?php

use Laravel\Jetstream\RedirectsActions;
use Laravel\Jetstream\InteractsWithBanner;

use function Livewire\Volt\{uses, computed, state, mount};

uses([
    RedirectsActions::class,
    InteractsWithBanner::class,
]);

state([
    'state' => [],
    'team' => null,
]);

mount(
    function ($team) {
        $this->team = $team;

        $this->state = [
            'team_database_id' => $team->team_database_id,
        ];
    }
);

$user = computed(fn() => Auth::user());

$teamDatabases = computed(fn() => $this->team->owner->teamDatabases);

$updateTeamDatabase = function () {

    $this->resetErrorBag();

    $this->team->forceFill([
        'team_database_id' => $this->state['team_database_id'],
    ])->save();

    $this->team->migrate()->configure()->use();

    $this->dispatch('saved');
};

?>

<x-form-section submit="updateTeamDatabase">
    <x-slot name="title">
        {{ __('Team Database') }}
    </x-slot>

    <x-slot name="description">
        {{ __('The team\'s database. You may select any one of your databases here and change it back at any time.') }}
    </x-slot>

    <x-slot name="form">

        <div class="col-span-6 sm:col-span-4">
            <x-label for="name" value="{{ __('Database') }}" />

            <x-select id="name" type="text" class="block w-full mt-1" wire:model="state.team_database_id" autofocus>
                @foreach ($this->teamDatabases as $teamDatabase)
                <option value="{{ $teamDatabase->id }}">{{ $teamDatabase->name }}</option>
                @endforeach
            </x-select>

            <x-input-error for="team_database_id" class="mt-2" />
        </div>

    </x-slot>

    @if (Gate::check('updateTeamDatabase', $team))
        <x-slot name="actions">
            <x-action-message class="me-3" on="saved">
                {{ __('Saved.') }}
            </x-action-message>

            <x-button>
                {{ __('Save') }}
            </x-button>
        </x-slot>
    @endif
</x-form-section>

        
        
