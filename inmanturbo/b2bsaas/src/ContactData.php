<?php

namespace Inmanturbo\B2bSaas;

use Illuminate\Support\Facades\Storage;
use Spatie\LaravelData\Data;

class ContactData extends Data implements Contact
{
    public function __construct(
        public ?AddressData $address = null,
        public ?string $name = null,
        public ?string $website = null,
        public ?string $email = null,
        public ?string $landingPage = null,
        public ?string $logoUrl = null,
        public ?string $logoPath = null,
        public ?string $phone = null,
        public ?string $fax = null,
        public ?string $type = 'Contact',
        public ?string $businessId = null,
        public ?string $uuid = null,
        public ?int $id = null,
        public ?string $teamUUID = null,
    ) {
    }

    public function teamUUID(): ?string
    {
        return $this->teamUUID ?? null;
    }

    public function uuid(): ?string
    {
        return $this->uuid ?? null;
    }

    public function businessId(): ?string
    {
        return $this->businessId ?? null;
    }

    public function id(): ?int
    {
        return $this->id ?? null;
    }

    public function type(): ?string
    {
        return $this->type ?? null;
    }

    public function name(): ?string
    {
        return $this->name ?? '';
    }

    public function address(): AddressData
    {
        return $this->address ?? new AddressData(
            city: config('b2bsaas.company.empty_city'),
            state: config('b2bsaas.company.empty_state'),
            zip: config('b2bsaas.company.empty_zip'),
            street: config('b2bsaas.company.empty_street'),
            country: config('b2bsaas.company.empty_country'),
            lineTwo: config('b2bsaas.company.empty_line_two'),
        );
    }

    public function streetAddress(): ?string
    {
        $streetAddress = $this->address?->street;
        if (isset($this->address->lineTwo) && strlen($this->address->lineTwo) > 1) {
            $streetAddress .= ', '.$this->address->lineTwo;
        }

        return $streetAddress;
    }

    public function cityStateZip(): ?string
    {
        if (! isset($this->address->city) || ! isset($this->address->state) || ! isset($this->address->zip) ||
            strlen($this->address->city) < 1 || strlen($this->address->state) < 1 || strlen($this->address->zip) < 1) {
            return '';
        }

        return $this->address->city.', '.$this->address->state.' '.$this->address->zip;
    }

    public function phone(): ?string
    {
        return $this->phone ?? config('b2bsaas.company.empty_phone');
    }

    public function rawPhone(): ?string
    {
        return $this->phone ?? null;
    }

    public function email(): ?string
    {
        return $this->email ?? '';
    }

    public function website(): ?string
    {
        return $this->website ?? '';
    }

    public function logoPath(): ?string
    {
        return $this->getLogoDisk()->path($this->logoPath ?? config('b2bsaas.company.empty_logo_path'));
    }

    public function logoUrl(): ?string
    {
        return $this->logoUrl;
    }

    public function fax(): ?string
    {
        return $this->fax ?? config('b2bsaas.company.empty_fax');
    }

    public function landingPage(): ?string
    {
        return $this->landingPage ?? null;
    }

    protected function getLogoDisk()
    {
        return Storage::disk(config('jetstream.profile_photo_disk', 'public'));
    }
}
