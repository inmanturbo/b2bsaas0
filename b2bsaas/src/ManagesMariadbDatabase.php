<?php

namespace B2bSaas;

trait ManagesMariadbDatabase {

    protected function getSystemDatabaseConnectionName(): string
    {
        return 'mariadb';
    }
}