<?php

namespace App;

enum UserType: string
{
    case SuperAdmin = Models\SuperAdmin::class;
    case User = Models\User::class;
    case UpgradedUser = Models\UpgradedUser::class;

    public static function toArray(): array
    {
        // programatically return associative array of enum cases
        return array_column(self::cases(), 'value', 'name');
    }
}
