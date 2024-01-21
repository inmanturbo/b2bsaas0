<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Inmanturbo\B2bSaas\HasParent;

class SuperAdmin extends User
{
    use HasFactory;
    use HasParent;

    public function hasPermissionTo($permission)
    {
        return true;
    }
}
