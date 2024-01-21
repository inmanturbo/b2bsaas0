<?php

namespace Inmanturbo\B2bSaas;

class PersonalAccessToken extends \Laravel\Sanctum\PersonalAccessToken
{
    use UsesLandlordConnection;
}
