<?php

namespace App\Policies;

use App\UserType;
use Illuminate\Auth\Access\HandlesAuthorization;
use Inmanturbo\B2bSaas\TeamDatabase;

class TeamDatabasePolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the given user can use the given database.
     *
     * @param  \App\User  $user
     * @return bool
     */
    public function use($user, TeamDatabase $teamDatabase)
    {
        // make sure the user owns the database or is a super admin
        return $user->id === $teamDatabase->user_id || $user->type === UserType::SuperAdmin->name;
    }
}
