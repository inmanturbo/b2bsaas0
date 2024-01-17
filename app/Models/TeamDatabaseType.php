<?php

namespace App\Models;

enum TeamDatabaseType: string
{
    case Mysql = MysqlTeamDatabase::class;
    case Sqlite = SqliteTeamDatabase::class;
    // case Postgres = PostgresTeamDatabase::class;
    // case SqlServer = SqlServerTeamDatabase::class;

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value', 'name');
    }

    public function createTeamDatabase(string $name, int|string $userId = null): TeamDatabase
    {
        $teamDatabaseModel = $this->value;

        return $teamDatabaseModel::create(
            [
                'name' => (string) str()->of($name)->slug('_'),
                'user_id' => $userId ?? (auth()?->id() ?? 1),
                'driver' => $this->name,
            ]
        );
    }
}
