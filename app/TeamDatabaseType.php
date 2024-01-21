<?php

namespace App;

use Inmanturbo\B2bSaas\ArrayableEnum;
use Inmanturbo\B2bSaas\HasEloquentModelableValue;
use Inmanturbo\B2bSaas\MariadbTeamDatabase;
use Inmanturbo\B2bSaas\MysqlTeamDatabase;
use Inmanturbo\B2bSaas\SqliteTeamDatabase;

enum TeamDatabaseType: string
{
    use ArrayableEnum;
    use HasEloquentModelableValue;

    case tenant_mysql = MysqlTeamDatabase::class;
    case tenant_mariadb = MariadbTeamDatabase::class;
    case tenant_sqlite = SqliteTeamDatabase::class;
    // case tenant_postgres = PostgresTeamDatabase::class;
    // case tenant_sqlserver = SqlServerTeamDatabase::class;
}
