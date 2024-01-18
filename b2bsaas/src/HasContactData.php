<?php

namespace B2bSaas;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Laravel\Jetstream\HasProfilePhoto;

trait HasContactData
{
    use HasProfilePhoto;

    public function contactData(): Attribute
    {
        return new Attribute(
            get: fn ($value, $attributes) => $this->getContactData($value, $attributes),
            set: fn ($value, $attributes) => json_encode($value),
        );
    }

    public function getContactData($value, $attributes)
    {
        $companyData = json_decode($attributes['contact_data'] ?? '', true);

        $address = new AddressData(
            city: $companyData['address']['city'] ?? null,
            state: $companyData['address']['state'] ?? null,
            zip: $companyData['address']['zip'] ?? null,
            street: $companyData['address']['street'] ?? null,
            country: $companyData['address']['country'] ?? 'USA',
            lineTwo: $companyData['address']['lineTwo'] ?? null,
        );

        return new ContactData(
            name: $companyData['name'] ?? $this->name,
            landingPage: $companyData['landingPage'] ?? null,
            address: $address,
            logoUrl: $this->profile_photo_url,
            logoPath: $this->profile_photo_path ?? config('b2bsaas.company.empty_logo_path'),
            phone: $companyData['phone'] ?? config('b2bsaas.company.empty_phone'),
            fax: $companyData['fax'] ?? config('b2bsaas.company.empty_phone'),
            email: $companyData['email'] ?? null,
            website: $companyData['website'] ?? null,
        );
    }
}
