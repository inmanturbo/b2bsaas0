<?php

namespace App;

use Inmanturbo\B2bSaas\ArrayableEnum;
use Inmanturbo\B2bSaas\HasEloquentModelableValue;

enum UserType: string
{
    use ArrayableEnum;
    use HasEloquentModelableValue;

    case SuperAdmin = Models\SuperAdmin::class;
    case User = Models\User::class;
    case UpgradedUser = Models\UpgradedUser::class;
}
