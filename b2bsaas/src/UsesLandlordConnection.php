<?php

namespace B2bSaas;

trait UsesLandlordConnection
{
    public function getConnectionName()
    {
        return config('database.landlord');
    }
}
