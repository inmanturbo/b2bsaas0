<?php

namespace App\Models;

enum TeamDatabaseTypes: string
{
    case Mysql = MysqlTeamDatabase::class;
    // case Postgres = PostgresTeamDatabase::class;
    // case Sqlite = SqliteTeamDatabase::class;
    // case SqlServer = SqlServerTeamDatabase::class;

    public static function toArray(): array
    {
        // programatically return associative array of enum cases
        return array_column(self::cases(), 'value', 'name');
    }
  
}