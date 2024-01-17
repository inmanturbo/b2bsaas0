<?php

namespace App\Models;

use B2bSaas\HasParent;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UpgradedUser extends User
{
    use HasFactory;
    use HasParent;
}
