<?php

// Path: b2bsaas/routes/web.php

use App\Models\Team;
use Illuminate\Support\Facades\Route;
use Inmanturbo\B2bSaas\CurrentTeamController;

Route::put('/current-team', [CurrentTeamController::class, 'update'])->name('current-team.update')->middleware([
    'web',
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
]);

Route::get('/teams/create', function () {
    return redirect()->route('teams.create');
});

Route::get('/teams/{team:uuid}', function ($team) {
    return redirect()->route('teams.show', $team);
});

Route::view('/v1/teams/create', 'b2bsaas::teams.create')->name('teams.create')->middleware([
    'web',
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
]);

Route::view('/v1/teams/{team:uuid}', 'b2bsaas::teams.show')->name('teams.show')->middleware([
    'web',
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
]);

Route::get('/v1/team-page/{team:slug}', function ($team) {

    $team = Team::where('slug', $team)->firstOrFail();

    // if the team has an html landing page, return it
    if ($team->contact_data?->landingPage()) {
        return response()->file(Storage::disk($team->landingPageDisk())->path($team->contact_data?->landingPage()));
    }

    return view('welcome');

})->name('teams.landing-page');

Route::get('/', function () {

    $team = isset(app()['team']) ? app()['team'] : null;

    // if the team has an html landing page, return it
    if ($team?->contact_data?->landingPage()) {
        return response()->file(Storage::disk($team->landingPageDisk())->path($team->contact_data?->landingPage()));
    }

    return view('welcome');

})->name('home');
