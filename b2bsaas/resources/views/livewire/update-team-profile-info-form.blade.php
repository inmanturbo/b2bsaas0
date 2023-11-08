<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use function Livewire\Volt\{state, usesFileUploads};

state([
    'photo' => null,
    'team' => null,
]);

usesFileUploads();

$updateTeamLogo = function () {
    Validator::make([$this?->photo], [
        'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
    ])->validateWithBag('updateProfileInformation');

    if ($this->photo) {
        DB::connection('landlord')->transaction(function () {
            $this->team->updateProfilePhoto($this->photo);
        });
    }

    $this->dispatch('saved');
};

$deleteProfilePhoto = function () {
    DB::connection('landlord')->transaction(function () {
        $this->team->deleteProfilePhoto();
    });

    $this->dispatch('saved');
};

?>

<x-form-section submit="updateTeamLogo">
    <x-slot name="title">
        {{ __('Team Logo') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Your Teams\'s Logo.') }}
    </x-slot>

    <x-slot name="form">
        <!-- Profile Photo -->
        
            <div x-data="{photoName: null, photoPreview: null}" class="col-span-6 sm:col-span-4">
                <!-- Profile Photo File Input -->
                @if(Gate::check('update', $team))
                <input type="file" class="hidden"
                            wire:model="photo"
                            x-ref="photo"
                            x-on:change="
                                    photoName = $refs.photo.files[0].name;
                                    const reader = new FileReader();
                                    reader.onload = (e) => {
                                        photoPreview = e.target.result;
                                    };
                                    reader.readAsDataURL($refs.photo.files[0]);
                            " />

                <x-label for="photo" value="{{ __('Logo') }}" />

                <!-- Current Profile Photo -->
                <div class="mt-2" x-show="! photoPreview">
                    <img src="{{ $team->profile_photo_url }}" alt="{{ $team->name }}" class="object-cover w-20 h-20 rounded-full">
                </div>

                <!-- New Profile Photo Preview -->
                <div class="mt-2" x-show="photoPreview" style="display: none;">
                    <span class="block w-20 h-20 bg-center bg-no-repeat bg-cover rounded-full"
                          x-bind:style="'background-image: url(\'' + photoPreview + '\');'">
                    </span>
                </div>

                @if(Gate::check('update', $team))
                <x-secondary-button class="mt-2 mr-2" type="button" x-on:click.prevent="$refs.photo.click()">
                    {{ __('Select A New Logo') }}
                </x-secondary-button>
                @endif

                @if ($team->profile_photo_path)
                    <x-secondary-button type="button" class="mt-2" wire:click="deleteProfilePhoto">
                        {{ __('Remove Logo') }}
                    </x-secondary-button>
                @endif

                <x-input-error for="photo" class="mt-2" />
            </div>
        @endif

    </x-slot>

    <x-slot name="actions">
        @if(Gate::check('update', $team))
        <x-action-message class="mr-3" on="saved">
            {{ __('Saved.') }}
        </x-action-message>

        <x-button wire:loading.attr="disabled" wire:target="photo">
            {{ __('Save') }}
        </x-button>
        @endif
    </x-slot>
</x-form-section>