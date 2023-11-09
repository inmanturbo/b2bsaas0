<?php

namespace App\Models;

enum UserType: string
{
    case SuperAdmin = SuperAdmin::class;
    case User = User::class;
    case UpgradedUser = UpgradedUser::class;

    public static function toArray(): array
    {
        // programatically return associative array of enum cases
        return array_column(self::cases(), 'value', 'name');
    }
}
