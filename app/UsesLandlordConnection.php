<?php

namespace App;

trait UsesLandlordConnection
{
    public function getConnectionName()
    {
        return config('database.landlord');
    }
}
