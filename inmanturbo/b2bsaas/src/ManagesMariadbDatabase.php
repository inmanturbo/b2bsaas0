<?php

namespace Inmanturbo\B2bSaas;

trait ManagesMariadbDatabase
{
    protected function getSystemDatabaseConnectionName(): string
    {
        return 'mariadb';
    }
}
