<?php

namespace App\Models;

use B2bSaas\HasParent;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SuperAdmin extends User
{
    use HasFactory;
    use HasParent;

    public function hasPermissionTo($permission)
    {
        return true;
    }
}
