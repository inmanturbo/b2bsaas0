<?php

namespace App\Models;

trait UsesLandlordConnection
{
    public function getConnectionName()
    {
        return 'landlord';
    }
}
