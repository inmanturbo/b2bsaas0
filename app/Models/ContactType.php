<?php

namespace App\Models;

enum ContactType: string
{
    case Contact = Contact::class;
    case Customer = Customer::class;
    case Vendor = Vendor::class;
    case TradePartner = TradePartner::class;

    public static function toArray(): array
    {
        // programatically return associative array of enum cases
        return array_column(self::cases(), 'value', 'name');
    }
}
