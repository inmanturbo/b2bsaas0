<?php

use App\Models\SqliteTeamDatabase;
use App\Models\User;

it('creates and deletes an sqlite database', function () {
    $user = User::factory()->create();

    $database = SqliteTeamDatabase::create([
        'user_id' => $user->id,
        'name' => 'test',
    ]);

    $this->assertFileExists(storage_path('app/'.$user->uuid.'/'.$database->name.'.sqlite'));

    $database->delete();

    $this->assertFileDoesNotExist(storage_path('app/'.$user->uuid.'/'.$database->name.'.sqlite'));
});
