<?php

namespace App\Models;

use B2bSaas\HasParent;

class MariadbTeamDatabase extends TeamDatabase
{
    use HasParent;

    public function getSystemDatabaseConnectionName(): string
    {
        return 'mariadb';
    }
}
