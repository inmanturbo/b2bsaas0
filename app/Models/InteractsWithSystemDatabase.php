<?php

namespace App\Models;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

trait InteractsWithSystemDatabase
{
    public function getSystemDatabaseName(): string
    {
        return 'mysql';
    }

    protected function deleteTeamDatabase()
    {
        $this->prepareTenantConnection($this->getSystemDatabaseName());

        $name = (string) str()->of($this->name)->slug('_');

        DB::statement('DROP DATABASE IF EXISTS '.$name);

        $this->restoreOriginalConnection();
    }

    protected function createTeamDatabase(bool $testing = false): self
    {
        $name = (string) str()->of($this->name)->slug('_');

        if ($this->teamDatabaseExists(testing: $testing)) {
            $name = $name.'_1';
            $this->name = $name;
            $this->createTeamDatabase(testing: $testing);
        }

        if (! $testing) {
            DB::connection($this->getSystemDatabaseName())->statement('CREATE DATABASE IF NOT EXISTS '.$name);
        }

        return $this;
    }

    protected function teamDatabaseExists(bool $testing = false): bool
    {
        $this->prepareTenantConnection($this->getSystemDatabaseName());

        if ($testing) {
            $this->restoreOriginalConnection();

            return false;
        }

        $exists = DB::connection($this->getSystemDatabaseName())->select(
            "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '".$this->name."'"
        );

        $this->restoreOriginalConnection();

        return count($exists) > 0;
    }

    protected function handleMigration()
    {
        Artisan::call('migrate', [
            '--force' => true,
        ]);

        return $this;
    }
}
