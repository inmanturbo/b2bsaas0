<?php

namespace B2bSaas\Http\Middleware;

use App;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

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

            // dd((new App\Models\Team)->getConnection(), (new App\Models\Team)->getConnectionName(), (new App\Models\Team)->getConnection()->getDatabaseName(), (new App\Models\Team)->getConnection()->getSchemaBuilder()->getConnection()->getDoctrineSchemaManager()->listTableNames());

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
            session()->put('default_connection', config('database.default'));
            session()->put('connection_details', config('database.connections.'.config('database.default')));
            session()->put('team_database', app('teamDatabase'));
            session()->put('team_database_tables', Schema::connection(config('database.default'))->getConnection()->getDoctrineSchemaManager()->listTableNames());
        }

        return $next($request);
    }
}
