<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class UpgradedUser extends User
{
    use HasFactory;
    use HasParent;
}
