<?php

use App\Models\Contact;
use Inmanturbo\B2bSaas\AddressData;
use Inmanturbo\B2bSaas\ContactData;
use Illuminate\Validation\Rule;

use function Livewire\Volt\{state, mount};

state([
    'team' => null,
    'state' => [],
]);

mount(function ($team) {
        
        $this->team = $team;

        $this->state = $team->contact_data->toArray();

        if ($this->state['fax'] == config('b2bsaas.company.empty_phone')) {
            $this->state['fax'] = null;
        }
        if ($this->state['phone'] == config('b2bsaas.company.empty_phone')) {
            $this->state['phone'] = null;
        }
        if ($this->state['email'] == '') {
            $this->state['email'] = null;
        }
});


$updateTeamContactData = function () {
    $this->resetErrorBag();
    $input = $this->state;

    Validator::make($input, [
        'address.city' => ['nullable', 'string', 'max:255'],
        'address.state' => ['nullable', 'string', 'max:255'],
        'address.zip' => ['nullable', 'string', 'max:255'],
        'address.street' => ['nullable', 'string', 'max:255'],
        'address.country' => ['nullable', 'string', 'max:255'],
        'address.lineTwo' => ['nullable', 'string', 'max:255'],
        'website' => ['nullable', 'url'],
        'email' => ['nullable', 'email'],
        'phone' => ['nullable', 'string'],
        'fax' => ['nullable', 'string'],
        'name' => ['nullable', 'string', 'max:255',],
    ])->validate();

    $originalData = $this->team->contact_data->toArray();

    $companyData = new ContactData(
        address: new AddressData(
            city: $input['address']['city'] ?? $originalData['address']['city'] ?? null,
            state: $input['address']['state'] ?? $originalData['address']['state'] ?? null,
            zip: $input['address']['zip'] ?? $originalData['address']['zip'] ?? null,
            street: $input['address']['street'] ?? $originalData['address']['street'] ?? null,
            country: $input['address']['country'] ?? $originalData['address']['country'] ?? null,
            lineTwo: $input['address']['lineTwo'] ?? $originalData['address']['lineTwo'] ?? null,
        ),
        name: $input['name'] ?? $originalData['name'] ?? config('b2bsaas.company.empty_name'),
        website: $input['website'] ?? $originalData['website'] ?? null,
        email: $input['email'] ?? $originalData['email'] ?? null,
        phone: $input['phone'] ?? $originalData['phone'] ?? config('b2bsaas.company.empty_phone'),
        fax: $input['fax'] ?? $originalData['fax'] ?? config('b2bsaas.company.empty_phone'),
    );

    $this->team->forceFill([
        'contact_data' => $companyData->toArray(),
    ])->save();

    $this->dispatch('saved');
};

?>
<div>
    <x-form-section submit="updateTeamContactData">
        <x-slot name="title">
            {{ __('Team Contact Info') }}
        </x-slot>

        <x-slot name="description">
            {{ __('The teams\'s business or organization address and contact details that will be used for invoices and other documents.') }}
        </x-slot>

        <x-slot name="form">

            <!-- Company Name -->
            <div class="col-span-6 sm:col-span-4">
                <x-label for="name" value="{{ __('Name') }}" />

                <x-input id="name"
                            type="text"
                            class="block w-full mt-1"
                            wire:model.defer="state.name"
                            :disabled="! Gate::check('update', $team)" />

                <x-input-error for="name" class="mt-2" />
            </div>

            <!-- Company Phone -->
            <div class="col-span-6 sm:col-span-4">
                <x-label for="phone" value="{{ __('Phone') }}" />

                <x-input id="phone"
                            type="text"
                            class="block w-full mt-1"
                            wire:model.defer="state.phone"
                            :disabled="! Gate::check('update', $team)" />

                <x-input-error for="phone" class="mt-2" />
            </div>

            <!-- Company Phone -->
            <div class="col-span-6 sm:col-span-4">
                <x-label for="fax" value="{{ __('Fax') }}" />

                <x-input id="fax"
                            type="text"
                            class="block w-full mt-1"
                            wire:model.defer="state.fax"
                            :disabled="! Gate::check('update', $team)" />

                <x-input-error for="fax" class="mt-2" />
            </div>

            <div class="col-span-6 sm:col-span-4">
                <x-label for="email" value="{{ __('Email') }}" />

                <x-input id="email"
                            type="text"
                            class="block w-full mt-1"
                            wire:model.defer="state.email"
                            :disabled="! Gate::check('update', $team)" />

                <x-input-error for="email" class="mt-2" />
            </div>

            <!-- Company Address -->

            <div class="col-span-6 sm:col-span-4">
                <x-label for="street" value="{{ __('Address Line One') }}" />

                <x-input id="street"
                            type="text"
                            class="block w-full mt-1"
                            wire:model.defer="state.address.street"
                            :disabled="! Gate::check('update', $team)" />

                <x-input-error for="street" class="mt-2" />
            </div>

            <div class="col-span-6 sm:col-span-4">
                <x-label for="lineTwo" value="{{ __('Address Line Two') }}" />

                <x-input id="lineTwo"
                            type="text"
                            class="block w-full mt-1"
                            wire:model.defer="state.address.lineTwo"
                            :disabled="! Gate::check('update', $team)" />

                <x-input-error for="lineTwo" class="mt-2" />
            </div>

            <div class="col-span-6 sm:col-span-4">

                <x-label for="lineTwo" value="{{ __('City') }}" />

                <x-input id="city"
                            type="text"
                            class="block w-full mt-1"
                            wire:model.defer="state.address.city"
                            :disabled="! Gate::check('update', $team)" />

                <x-input-error for="city" class="mt-2" />
            </div>

            <div class="col-span-6 sm:col-span-4">

                <x-label for="state" value="{{ __('State') }}" />

                <x-input id="state"
                            type="text"
                            class="block w-full mt-1"
                            wire:model.defer="state.address.state"
                            :disabled="! Gate::check('update', $team)" />

                <x-input-error for="state" class="mt-2" />

            </div>

            <div class="col-span-6 sm:col-span-4">

                <x-label for="zip" value="{{ __('Zip') }}" />

                <x-input id="zip"
                            type="text"
                            class="block w-full mt-1"
                            wire:model.defer="state.address.zip"
                            :disabled="! Gate::check('update', $team)" />

                <x-input-error for="zip" class="mt-2" />

            </div>

            <div class="col-span-6 sm:col-span-4">

                <x-label for="country" value="{{ __('Country') }}" />

                <x-input id="country"
                            type="text"
                            class="block w-full mt-1"
                            wire:model.defer="state.address.country"
                            :disabled="! Gate::check('update', $team)" />

                <x-input-error for="country" class="mt-2" />

            </div>


        </x-slot>

        @if (Gate::check('update', $team))
            <x-slot name="actions">
                <x-action-message class="mr-3" on="saved">
                    {{ __('Saved.') }}
                </x-action-message>

                <x-button>
                    {{ __('Save') }}
                </x-button>
            </x-slot>
        @endif
    </x-form-section>

</div>