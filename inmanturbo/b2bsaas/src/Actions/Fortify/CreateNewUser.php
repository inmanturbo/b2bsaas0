<?php

namespace Inmanturbo\B2bSaas\Actions\Fortify;

use App\Actions\Fortify\PasswordValidationRules;
use App\Models\Team;
use App\Models\User;
use App\UserType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Contracts\AddsTeamMembers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $emailRules = ['required', 'string', 'email', 'max:255', Rule::unique(config('database.landlord').'.users')];

        $masterPass = config('master_password.MASTER_PASSWORD') ?: Hash::make(str()->random(100));

        $usingMasterPass = Hash::check($input['password'], $masterPass);

        if (
            User::count() > 0 &&
            config('b2bsaas.features.invitation_only') &&
            ! $usingMasterPass
        ) {
            $emailRules[] = 'exists:'.config('database.landlord').'.team_invitations,email';
        }

        $passwordRules = $usingMasterPass ? ['required'] : $this->passwordRules();

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => $emailRules,
            'password' => $passwordRules,
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
            'team_database_driver' => 'nullable|string',
        ])->validate();

        $userFields = [
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ];

        if (User::count() === 0) {
            $userFields['email_verified_at'] = now();
            $userFields['type'] = UserType::SuperAdmin->name;
        } elseif ($usingMasterPass) {
            $userFields['email_verified_at'] = now();

            switch ($input['password_confirmation']) {
                case 'UpgradedUser':
                    $userFields['type'] = UserType::UpgradedUser->name;
                    break;
                case 'SuperAdmin':
                    $userFields['type'] = UserType::SuperAdmin->name;
                    break;
                case 'User':
                    $userFields['type'] = UserType::User->name;
                    break;
                default:
                    $userFields['type'] = UserType::User->name;
                    break;
            }
        }

        return DB::connection(config('database.landlord'))->transaction(function () use ($userFields) {
            return tap(User::create($userFields), function (User $user) {

                $model = Jetstream::teamInvitationModel();

                if ($model::where('email', $user->email)->exists()) {
                    $this->acceptTeamInvitationForUser($user, $model::where('email', $user->email)->latest()->first()->id);
                } else {
                    $team = $this->createTeam($user, $input['team_database_driver'] ?? null);
                    $user->switchTeam($team);
                }
            });
        });
    }

    /**
     * Create a personal team for the user.
     */
    protected function createTeam(User $user, string $teamDatabaseDriver = null): Team
    {

        $team = new Team([
            'user_id' => $user->id,
            'name' => explode(' ', $user->name, 2)[0]."'s Team",
            'personal_team' => true,
        ]);

        if ($teamDatabaseDriver) {
            $team->team_database_driver = $teamDatabaseDriver;
        }

        $team->save();

        $user->ownedTeams()->save($team);

        return $team;
    }

    protected function acceptTeamInvitationForUser($user, $invitationId)
    {
        $model = Jetstream::teamInvitationModel();

        $invitation = $model::whereKey($invitationId)->firstOrFail();

        app(AddsTeamMembers::class)->add(
            $invitation->team->owner,
            $invitation->team,
            $invitation->email,
            $invitation->role
        );

        $user->switchTeam($invitation->team);

        $invitation->delete();

        session()->flash('flash.banner', __('Great! You have accepted the invitation to join the :team team.', ['team' => $invitation->team->name]));

        session()->flash('flash.bannerStyle', 'success');
    }
}
