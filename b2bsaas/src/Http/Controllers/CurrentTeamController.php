<?php

namespace B2bSaas\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Laravel\Jetstream\Jetstream;

class CurrentTeamController extends Controller
{
    /**
     * Update the authenticated user's current team.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $team = Jetstream::newTeamModel()->where('uuid', $request->team_uuid)->firstOrFail();

        if (! $request->user()->switchTeam($team)) {
            abort(403);
        }

        return redirect(config('fortify.home'), 303);
    }
}
