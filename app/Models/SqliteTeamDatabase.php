<?php

namespace App\Models;

use Artisan;
use Illuminate\Support\Facades\Storage;

class SqliteTeamDatabase extends TeamDatabase
{
    use HasParent;

    protected function createTeamDatabase(bool $testing = false): self
    {
        $name = (string) str()->of($this->name)->slug('_');

        if ($this->teamDatabaseExists(testing: $testing)) {
            $name = $name.'_1';
            $this->name = $name;
            $this->createTeamDatabase(testing: $testing);
        }

        $userUuid = (string) $this->user->uuid;

        // create storage directory for user if it doesn't exist
        if (! file_exists(storage_path('app/'.$userUuid))) {
            mkdir(storage_path('app/'.$userUuid));
        }

        if (! file_exists(storage_path('app/'.$userUuid.'/'.$name.'.sqlite'))) {
            Storage::disk('local')->put($userUuid.'/'.$name.'.sqlite', '');
        }

        return $this;
    }

    protected function deleteTeamDatabase()
    {
        $name = (string) str()->of($this->name)->slug('_');

        $userUuid = (string) $this->user->uuid;

        if (file_exists(storage_path('app/'.$userUuid.'/'.$name.'.sqlite'))) {
            unlink(storage_path('app/'.$userUuid.'/'.$name.'.sqlite'));
        }
    }

    protected function teamDatabaseExists(bool $testing = false): bool
    {
        $name = (string) str()->of($this->name)->slug('_');

        $userUuid = (string) $this->user->uuid;

        return file_exists(storage_path('app/'.$userUuid.'/'.$name.'.sqlite'));
    }

    protected function handleMigration()
    {
        Artisan::call('migrate', [
            '--force' => true,
        ]);
    }

    protected function getTenantConnectionDatabaseName(): string
    {
        $name = (string) str()->of($this->name)->slug('_');

        $userUuid = (string) $this->user->uuid;

        return storage_path('app/'.$userUuid.'/'.$name.'.sqlite');
    }
}
