<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Inmanturbo\B2bSaas\HasParent;

class UpgradedUser extends User
{
    use HasFactory;
    use HasParent;
}
