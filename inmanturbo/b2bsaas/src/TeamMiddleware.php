<?php

namespace Inmanturbo\B2bSaas;

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

        if (! app()->runningUnitTests()) {

            $team = $request->user()?->currentTeam;

            if (! $team) {
                return $next($request);
            }

            if (! cache()->has('team_migrated_'.$team->id)) {
                $team->migrate();
                cache()->put('team_migrated_'.$team->id, true, now()->addDay());
            }

            $team->configure()->use();
        }

        if (config('app.debug')) {
            Log::debug('Team authenticated');
            session()->put('default_connection', config('database.default'));
            session()->put('connection_details', config('database.connections.'.config('database.default')));
        }

        return $next($request);
    }
}
