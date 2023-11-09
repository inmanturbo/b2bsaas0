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

        DB::connection($this->tenantConnection)
            ->statement('DROP DATABASE IF EXISTS ' . $name);
    }

    protected function createTeamDatabase(): self
    {

        $this->prepareTenantConnection($this->getSystemDatabaseName());

        $name = (string) str()->of($this->name)->slug('_');

        if ($this->teamDatabaseExists()) {
            $name = $name . '_1';
            $this->name = $name;
            $this->createTeamDatabase();
        }

        DB::connection($this->tenantConnection)
            ->statement('CREATE DATABASE IF NOT EXISTS ' . $name);

        $this->prepareTenantConnection($name);

        return $this;
    }

    protected function teamDatabaseExists(): bool
    {

        $exists = DB::connection($this->tenantConnection)
            ->select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . $this->name . "'");

        return count($exists) > 0;
    }

    protected function handleMigration()
    {
        Artisan::call('migrate', [
            '--database' => $this->tenantConnection,
        ]);

        return $this;
    }
}
