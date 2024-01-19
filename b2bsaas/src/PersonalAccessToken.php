<?php

namespace B2bSaas;

class PersonalAccessToken extends \Laravel\Sanctum\PersonalAccessToken
{
    use UsesLandlordConnection;
}
