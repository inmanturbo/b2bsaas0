<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

trait HasTeamDatabase
{
    public function bootHasTeamDatabase()
    {
        parent::boot();

        static::creating(function (Model $model) {
            if (! $model->team_database_id) {
                $teamDatabase = $model->createTeamDatabase();

                $model->team_database_id = $teamDatabase->id;
            }
            $model->slug = (string) str()->of($model->name)->slug('-');
        });

        static::updating(function (Model $model) {
            $model->slug = (string) str()->of($model->name)->slug('-');
        });
    }

    public function migrate()
    {
        $this->teamDatabase->migrate();

        return $this;
    }

    public function teamDatabase(): BelongsTo
    {
        return $this->belongsTo(TeamDatabase::class);
    }

    protected function createTeamDatabase(): TeamDatabase
    {
        $column = Schema::connection($this->getConnectionName())->getConnection()->getDoctrineColumn('team_databases', 'driver');
        $driver = $column->getDefault();

        switch ($driver) {
            case TeamDatabaseType::Sqlite->name:
                $teamDatabase = SqliteTeamDatabase::create(
                    [
                        'name' => (string) str()->of($this->name)->slug('_'),
                        'user_id' => $this?->user_id ?? (auth()?->id() ?? 1),
                        'driver' => TeamDatabaseType::Sqlite->name,
                    ]
                );
                break;
            case TeamDatabaseType::Mysql->name:
                $teamDatabase = MysqlTeamDatabase::create(
                    [
                        'name' => (string) str()->of($this->name)->slug('_'),
                        'user_id' => $this?->user_id ?? (auth()?->id() ?? 1),
                        'driver' => TeamDatabaseType::Mysql->name,
                    ]
                );
                break;
            default:
                throw new \Exception('Unsupported database driver');
        }

        return $teamDatabase;
    }
}
