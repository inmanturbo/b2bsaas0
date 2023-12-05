<?php

namespace B2bSaas\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TeamMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  string[]  ...$guards
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle(Request $request, Closure $next)
    {
        // if not running unit tests
        if (! app()->runningUnitTests()) {

            $team = $request->user()?->currentTeam;

            if (! $team) {
                return $next($request);
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

        // if app debug is true, log team authenticated
        if (config('app.debug')) {
            Log::debug('Team authenticated');
        }

        return $next($request);
    }
}
