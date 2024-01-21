<?php

namespace App\Models;

use Inmanturbo\B2bSaas\UsesLandlordConnection;
use Laravel\Jetstream\Membership as JetstreamMembership;

class Membership extends JetstreamMembership
{
    use UsesLandlordConnection;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;
}
