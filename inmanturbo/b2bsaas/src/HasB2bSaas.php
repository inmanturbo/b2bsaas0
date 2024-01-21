<?php

namespace Inmanturbo\B2bSaas;

use App\UserType;
use Laravel\Jetstream\HasTeams;

trait HasB2bSaas
{
    use HasChildren;
    use HasTeams {
        switchTeam as jetstreamSwitchTeam;
    }

    public function getChildTypes()
    {
        return UserType::toArray();
    }

    public function teamDatabases()
    {
        return $this->hasMany(TeamDatabase::class);
    }

    /**
     * Switch the user's context to the given team.
     *
     * @param  mixed  $team
     */
    public function switchTeam($team): bool
    {
        $isOnTeam = $this->jetstreamSwitchTeam($team);

        $team?->configure()?->use();

        return $isOnTeam;
    }
}
