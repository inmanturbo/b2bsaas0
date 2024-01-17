<?php

namespace B2bSaas;

use App\Models;

enum TeamDatabaseType: string
{
    case tenant_mysql = Models\MysqlTeamDatabase::class;
    case tenant_mariadb = Models\MariadbTeamDatabase::class;
    case tenant_sqlite = Models\SqliteTeamDatabase::class;
    // case Postgres = PostgresTeamDatabase::class;
    // case SqlServer = SqlServerTeamDatabase::class;

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value', 'name');
    }

    public function createTeamDatabase(string $name, int|string $userId = null): Models\TeamDatabase
    {
        $teamDatabaseModel = $this->value;

        return $teamDatabaseModel::create(
            [
                'name' => (string) str()->of($name)->slug('_'),
                'user_id' => $userId ?? (auth()?->id() ?? 1),
                'connection_template' => $this->name,
            ]
        );
    }
}
