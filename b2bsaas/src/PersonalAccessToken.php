<?php

namespace B2bSaas;

use B2bSaas\UsesLandlordConnection;

class PersonalAccessToken extends \Laravel\Sanctum\PersonalAccessToken
{
    use UsesLandlordConnection;
}
