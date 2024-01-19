<?php

namespace B2bSaas;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

trait ManagesSqliteDatabase
{
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

        if (file_exists($file = storage_path('app/'.$userUuid.'/'.$name.'.sqlite'))) {
            unlink($file);
        }
    }

    protected function teamDatabaseExists(bool $testing = false): bool
    {
        $name = (string) str()->of($this->name)->slug('_');

        $userUuid = (string) $this->user->uuid;

        return file_exists($file = storage_path('app/'.$userUuid.'/'.$name.'.sqlite'));
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
