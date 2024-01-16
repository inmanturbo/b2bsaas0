<?php

namespace B2bSaas\Actions\Jetstream;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Laravel\Jetstream\Contracts\CreatesTeams;
use Laravel\Jetstream\Events\AddingTeam;
use Laravel\Jetstream\Jetstream;

class CreateTeam implements CreatesTeams
{
    /**
     * Validate and create a new team for the given user.
     *
     * @param  array<string, string>  $input
     */
    public function create(User $user, array $input): Team
    {
        Gate::forUser($user)->authorize('create', Jetstream::newTeamModel());

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'team_database_id' => ['nullable', 'integer', 'exists:'.config('database.landlord').'.team_databases,id'],
            'team_database_driver' => 'nullable|string',
        ])->validateWithBag('createTeam');

        AddingTeam::dispatch($user);

        $teamData = [
            'name' => $input['name'],
            'personal_team' => false,
        ];

        if (isset($input['team_database_id'])) {
            $teamData['team_database_id'] = $input['team_database_id'];
        }

        $user->switchTeam($team = $user->ownedTeams()->save(
            $this->createTeamForUser($user, $teamData, $input['team_database_driver'] ?? null
            )));

        return $team;
    }

    protected function createTeamForUser($user, array $teamData, string $teamDatbaseDriver = null): Team
    {

        $teamData = array_merge($teamData, [
            'user_id' => $user->id,
        ]);

        $team = new Team($teamData);

        if ($teamDatbaseDriver) {
            $team->team_database_driver = $teamDatbaseDriver;
        }

        $team->save();

        return $team;
    }

    public function redirectTo(): string
    {
        return route('teams.show', ['team' => auth()->user()->currentTeam->uuid]);
    }
}
