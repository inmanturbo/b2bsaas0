<?php

namespace B2bSaas;

use App\Models\PersonalAccessToken;
use App\Models\Team;
use B2bSaas\Actions\Fortify\CreateNewUser;
use B2bSaas\Actions\Fortify\UpdateUserProfileInformation;
use B2bSaas\Actions\Jetstream\AddTeamMember;
use B2bSaas\Actions\Jetstream\InviteTeamMember;
use Illuminate\Queue\Events\JobProcessing;
use Laravel\Fortify\Fortify;
use Laravel\Jetstream\Jetstream;
use Laravel\Sanctum\Sanctum;

class B2bSaasServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {

        Sanctum::ignoreMigrations();

        $this->mergeConfigFrom(
            __DIR__.'/../config/b2bsaas.php',
            'b2bsaas'
        );
    }

    public function boot()
    {

        $this->configureRequests();

        $this->configureQueue();

        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);

        Jetstream::inviteTeamMembersUsing(InviteTeamMember::class);
        Jetstream::addTeamMembersUsing(AddTeamMember::class);
        Jetstream::deleteUsersUsing(Actions\Jetstream\DeleteUser::class);
        Jetstream::createTeamsUsing(Actions\Jetstream\CreateTeam::class);

        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        //if the config('app.url_scheme') is set to https, then we will force the scheme to be https
        if (config('b2bsaas.url_scheme') === 'https') {
            \URL::forceScheme('https');
        }

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'b2bsaas');
    }

    public function configureRequests()
    {
        if (! $this->app->runningInConsole()) {
            $domain = $this->app->request->getHost();

            /** @var \App\Models\Team $team
             * query to see if a team owns the current domain
             */
            $team = Team::where('domain', $domain)->first();

            if (isset($team->id) && isset($team->team_database_id)) {

                // migrate only once a day, cache a key to check if it has been done today
                if (! cache()->has('team_migrated_'.$team->id)) {
                    $team
                        ->migrate();
                    cache()->put('team_migrated_'.$team->id, true, now()->addDay());
                }

                $team->configure()->use();
            }
        }
    }

    public function configureQueue()
    {
        if (isset($this->app['team'])) {
            $this->app['queue']->createPayloadUsing(function () {
                return $this->app['team'] ? [
                    'team_uuid' => $this->app['team']->uuid,
                ] : [];
            });
        }

        $this->app['events']->listen(JobProcessing::class, function ($event) {
            if (isset($event->job->payload['team_uuid'])) {
                $team = Team::whereUuid($event->job->payload['team_uuid'])->first();
                if (isset($team->id)) {
                    $team->configure()->use();
                }
            }
        });
    }
}
