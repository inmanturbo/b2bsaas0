<?php

namespace B2bSaas;

trait ArrayableEnum
{
    public static function toArray(): array
    {
        // programatically return associative array of enum cases
        return array_column(self::cases(), 'value', 'name');
    }
}
