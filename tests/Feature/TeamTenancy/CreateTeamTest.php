<?php

use App\Models\Team;
use App\Models\User;
use B2bSaas\UserType;
use Illuminate\Support\Facades\Schema;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertTrue;

it('creates and migrates team database when a teams is created', function () {

    actingAs($user = User::factory([
        'type' => UserType::SuperAdmin->name,
    ])->create());

    $team = Team::create([
        'name' => 'Test Team',
        'personal_team' => false,
        'user_id' => $user->fresh()->id,
    ]);

    assertDatabaseHas('team_databases', [
        'name' => $name = (string) str()->of($team->name)->slug('_'),
    ], 'testing_landlord');

    $team->configure()->use();

    assertTrue(Schema::connection($name)->hasTable('migrations'));
});
