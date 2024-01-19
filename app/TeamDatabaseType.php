<?php

namespace App;

use B2bSaas\ArrayableEnum;
use B2bSaas\HasEloquentModelableValue;
use B2bSaas\MariadbTeamDatabase;
use B2bSaas\MysqlTeamDatabase;
use B2bSaas\SqliteTeamDatabase;

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
