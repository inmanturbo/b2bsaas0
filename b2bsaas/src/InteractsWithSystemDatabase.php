<?php

namespace B2bSaas;

use Envor\DatabaseManager\Contracts\DatabaseManager as DatabaseManagerContract;
use Envor\DatabaseManager\DatabaseManager;
use Illuminate\Support\Facades\DB;

trait InteractsWithSystemDatabase
{
    use ConfiguresTenantDatabase;

    public function getSystemDatabaseConnectionName(): string
    {
        return 'mysql';
    }

    protected function databaseManager(): DatabaseManagerContract|DatabaseManager
    {
        return (new DatabaseManager)
            ->manage('mysql')
            ->setConnection($this->getSystemDatabaseConnectionName());
    }

    protected function deleteTeamDatabase()
    {

        $this->databaseManager()->deleteDatabase($name = (string) str()->of($this->name)->slug('_'));

        // $this->prepareTenantConnection($this->getSystemDatabaseConnectionName());

        // $name = (string) str()->of($this->name)->slug('_');

        // DB::statement('DROP DATABASE IF EXISTS '.$name);

        // $this->restoreOriginalConnection();
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
            // DB::connection($this->getSystemDatabaseConnectionName())->statement('CREATE DATABASE IF NOT EXISTS tenant_'.$name);
            $this->databaseManager()->createDatabase('tenant_'.$name);
        }

        $this->save();
        
        return $this;
    }

    protected function teamDatabaseExists(bool $testing = false): bool
    {
        // $this->prepareTenantConnection($this->getSystemDatabaseConnectionName());

        if ($testing) {
            // $this->restoreOriginalConnection();

            return false;
        }

        // $exists = DB::connection($this->getSystemDatabaseConnectionName())->select(
        //     "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'tenant_".$this->name."'"
        // );

        // $this->restoreOriginalConnection();

        // return count($exists) > 0;

        return $this->databaseManager()->databaseExists($name = (string) str()->of($this->name)->slug('_')->start('tenant_'));
    }
}
