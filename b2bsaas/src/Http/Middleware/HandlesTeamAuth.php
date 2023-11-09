<?php

namespace B2bSaas\Http\Middleware;

use Closure;

trait HandlesTeamAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string[]  ...$guards
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next, ...$guards)
    {
        $this->authenticate($request, $guards);

        // if not running unit tests
        if (! app()->runningUnitTests()) {

            $team = $request->user()?->currentTeam;

            if (! $team) {
                $this->unauthenticated($request, $guards);
            }

            // migrate only once a day, cache a key to check if it has been done today
            if (! cache()->has('team_migrated_'.$team->id)) {
                $team
                    ->migrate();
                cache()->put('team_migrated_'.$team->id, true, now()->addDay());
            }

            $team
            // ->migrate()
                ->configure()
                ->use();
        }

        return $next($request);
    }
}
