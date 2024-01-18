<?php

namespace App\Models;

use App\UsesLandlordConnection;

class PersonalAccessToken extends \Laravel\Sanctum\PersonalAccessToken
{
    use UsesLandlordConnection;
}
