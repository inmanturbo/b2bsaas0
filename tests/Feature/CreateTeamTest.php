<?php

use App\Models\User;
use App\Models\UserType;
use Laravel\Jetstream\Http\Livewire\CreateTeamForm;
use Livewire\Livewire;

use function Pest\Laravel\withoutExceptionHandling;

test('teams can be created', function () {

    withoutExceptionHandling();

    $this->actingAs($user = User::factory([
        'type' => UserType::SuperAdmin->name,
    ])->withPersonalTeam()->create());

    Livewire::test(CreateTeamForm::class)
        ->set(['state' => ['name' => 'Test Team']])
        ->call('createTeam');

    expect($user->fresh()->ownedTeams)->toHaveCount(2);
    expect($user->fresh()->ownedTeams()->latest('id')->first()->name)->toEqual('Test Team');
});
