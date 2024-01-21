<?php

use Laravel\Jetstream\RedirectsActions;
use Laravel\Jetstream\InteractsWithBanner;
use App\Models\TeamDatabase;

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
            'team_database_uuid' => $team->teamDatabase->uuid,
        ];
    }
);

$user = computed(fn() => Auth::user());

$teamDatabases = computed(fn() => $this->team->owner->teamDatabases);

$updateTeamDatabase = function () {

    $this->resetErrorBag();

    $this->validate([
        'state.team_database_uuid' => ['required','exists:'.config('database.landlord').'.team_databases,uuid'],
    ]);

    $teamDatabase = TeamDatabase::where('uuid', $this->state['team_database_uuid'])->firstOrFail();

    $this->authorize('use', $teamDatabase);

    $this->team->forceFill([
        'team_database_id' => $teamDatabase->id,
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

            <x-select id="name" type="text" class="block w-full mt-1" wire:model="state.team_database_uuid" autofocus>
                @foreach ($this->teamDatabases as $teamDatabase)
                <option value="{{ $teamDatabase->uuid }}">{{ $teamDatabase->name }}</option>
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

        
        
