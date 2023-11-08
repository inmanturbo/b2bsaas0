<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SuperAdmin extends User
{
    use HasFactory;
    use HasParent;

    public function orgs()
    {
        return $this->hasMany(Org::class)->orWhereRaw(1);
    }

    /**
     * Determine if the user has the given role on the given org.
     *
     * @param  mixed  $org
     * @return bool
     */
    public function hasOrgRole($org, string $role)
    {
        return true;
    }

    public function hasPermissionTo($permission)
    {
        return true;
    }
}
