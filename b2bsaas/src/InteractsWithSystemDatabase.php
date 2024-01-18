<?php

namespace B2bSaas;

use Illuminate\Support\Facades\DB;

trait InteractsWithSystemDatabase
{
    use ConfiguresTenantDatabase;

    public function getSystemDatabaseConnectionName(): string
    {
        return 'mysql';
    }

    protected function deleteTeamDatabase()
    {
        $this->prepareTenantConnection($this->getSystemDatabaseConnectionName());

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
            DB::connection($this->getSystemDatabaseConnectionName())->statement('CREATE DATABASE IF NOT EXISTS tenant_'.$name);
        }

        return $this;
    }

    protected function teamDatabaseExists(bool $testing = false): bool
    {
        $this->prepareTenantConnection($this->getSystemDatabaseConnectionName());

        if ($testing) {
            $this->restoreOriginalConnection();

            return false;
        }

        $exists = DB::connection($this->getSystemDatabaseConnectionName())->select(
            "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '".$this->name."'"
        );

        $this->restoreOriginalConnection();

        return count($exists) > 0;
    }
}
