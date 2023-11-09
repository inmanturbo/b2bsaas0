<?php

namespace App\Models;

class PersonalAccessToken extends \Laravel\Sanctum\PersonalAccessToken
{
    use UsesLandlordConnection;
}
