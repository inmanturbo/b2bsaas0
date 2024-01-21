<?php

use Illuminate\Support\Facades\DB;

use function Livewire\Volt\{state, mount, usesFileUploads};

usesFileUploads();

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

$updateTeamLandingPage = function () {
    $this->resetErrorBag();
    $input = $this->state;

    Validator::make(
        $input,
        [
            'landing_page' => ['nullable', 'file', 'mimes:html'],
        ]
    )->validate();

    if(isset($input['landing_page']) && $input['landing_page'] != null) {
        DB::connection(config('database.landlord'))->transaction(
            function () use ($input) {
                $this->team->updateLandingPage($input['landing_page']);
            }
        );

        $this->dispatch('saved');
    }

};

$downloadLandingPage = function () {
    return response()->download(
        Storage::disk($this->team->landingPageDisk())->path($this->team->contact_data->landingPage())
    );
};

$deleteLandingPage = function () {
    $this->team->deleteLandingPage();

    $this->dispatch('saved');
};

?>


<x-form-section submit="updateTeamLandingPage">
    <x-slot name="title">
        {{ __('Team Landing Page') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Your Team\'s Landing Page. You may customize it by uploading an html file.') }}
    </x-slot>

    <x-slot name="form">
        <!-- Profile Photo -->
        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
            <div x-data="{pageName: null, pagePreview: null}" class="col-span-6 sm:col-span-4">
                <!-- Profile Photo File Input -->
                @if(Gate::allows('update', $this->team))
                <input type="file" class="hidden"
                            wire:model="state.landing_page"
                            x-ref="page"
                            x-on:change="
                                    pageName = $refs.page.files[0].name;
                                    const reader = new FileReader();
                                    reader.onload = (e) => {
                                        pagePreview = e.target.result;
                                    };
                                    reader.readAsDataURL($refs.page.files[0]);
                            " />
                @endif

                <x-label for="landing_page" value="{{ __('Landing Page') }}" />
                <div class="">

                    <!-- Current Profile Photo -->
                    <div class="mt-2" x-show="! pagePreview">
                        <iframe 
                            src="{{ $this->team->landing_page_url }}" 
                            alt="{{ $this->team->name }}" 
                            class="w-full h-screen"
                            ></iframe>
                    </div>
                    
                    <!-- New Profile Photo Preview -->
                    <div class="mt-2" x-show="pagePreview" style="display: none;">
                        <iframe class="w-100" :src="pagePreview">
                        </iframe>
                    </div>
                </div>
                    
                <div class="col-span-6 sm:col-span-4">

                    @if(Gate::check('update', $this->team))
                    <x-secondary-button class="mt-2 mr-2" type="button" x-on:click.prevent="$refs.page.click()">
                        <span>{{ __('Replace') }}</span>
                    </x-secondary-button>
                    
                    @endif
                    
                    @if ($this->team?->contact_data?->landingPage())
                    <x-secondary-button type="button" class="mt-2 mr-2" wire:click="downloadLandingPage" x-on:click="pagePreview=false" >
                        <span>{{ __('Download') }}</span>
                    </x-secondary-button>

                    <x-secondary-button type="button" class="mt-2 mr-2" wire:click="deleteLandingPage" x-on:click="pagePreview=false" >
                        Delete
                    </x-secondary-button>
                    @endif
                    
                    {{-- <x-secondary-button-link class="mt-2 mr-2" target="_blank" href="/template">
                        <span>Template</span>
                    </x-secondary-button-link> --}}

                    <x-secondary-button-link class="mt-2" target="_blank" href="{{ $this->team->url }}">
                        <span>Visit</span>
                    </x-secondary-button-link>
                </div>

                <x-input-error for="landing_page" class="mt-2" />
            </div>
        @endif

    </x-slot>

    <x-slot name="actions">
        @if(Gate::check('update', $this->team))
        <x-action-message class="mr-3" on="saved">
            {{ __('Saved.') }}
        </x-action-message>

        <x-button wire:loading.attr="disabled" wire:target="page">
            {{ __('Save') }}
        </x-button>
        @endif
    </x-slot>
</x-form-section>
