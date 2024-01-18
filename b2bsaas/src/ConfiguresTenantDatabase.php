<?php

namespace B2bSaas;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait ConfiguresTenantDatabase
{
    public $originalConfig = [];

    public $originalDefaultConnectionName = null;

    public function configure()
    {
        $this->prepareTenantConnection($connection = $this->createTenantConnection());

        return $this;
    }

    public function use()
    {
        app()->forgetInstance('teamDatabase');

        app()->instance('teamDatabase', $this);
    }

    protected function restoreOriginalConnection(): void
    {
        if (! empty($this->originalConfig)) {
            config()->set('database.connections.'.$this->originalDefaultConnectionName, $this->originalConfig);
            config()->set('database.default', $this->originalDefaultConnectionName);
        }
    }

    protected function createTenantConnection(): string
    {
        if (! app()->runningUnitTests()) {

            $connectionTemplate = (string) str()->of($this->connection_template)->lower();

            $connectionTemplate = config('database.connections.'.$connectionTemplate);

        } else {
            $connectionTemplate = config('database.connections.testing_tenant');
        }
        $databaseConfig = [];

        if (! config('b2bsaas.database_creation_disabled') && ! app()->runningUnitTests()) {
            $databaseConfig['database'] = $this->getTenantConnectionDatabaseName();
        }

        config()->set('database.connections.'.$this->name, array_merge($connectionTemplate, $databaseConfig));

        return $this->name;
    }

    protected function getTenantConnectionDatabaseName(): string
    {
        return 'tenant_'.$this->name;
    }

    protected function prepareTenantConnection($name): void
    {
        $default = once(fn () => config('database.default'));

        $this->originalDefaultConnectionName = $default;

        $this->originalConfig = once(fn () => config('database.connections.'.$this->originalDefaultConnectionName));

        config()->set('database.default', $name);

        DB::purge();

        DB::reconnect();

        Schema::connection(config('database.default'))->getConnection()->reconnect();
    }
}
