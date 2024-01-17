<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

trait HasTeamDatabase
{

    public $teamDatabaseDriver = null;

    public static function bootHasTeamDatabase()
    {
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

    protected function getDefaultTeamDatabaseDriverName(): string
    {
        $column = Schema::connection($this->getConnectionName())->getConnection()->getDoctrineColumn('team_databases', 'driver');
        $driver = $column->getDefault();

        return $driver;
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

    protected function createTeamDatabase(TeamDatabaseType $driver = null): TeamDatabase
    {

        if (! $driver) {

            $defaultDriverName = $this->getDefaultTeamDatabaseDriverName();

            $driver = $this->teamDatabaseDriver
                ? constant(TeamDatabaseType::class.'::'.$this->teamDatabaseDriver)
                : constant(TeamDatabaseType::class.'::'.$defaultDriverName);
        }

        return $driver->createTeamDatabase(
            name: $this->name,
            userId: $this?->user_id ?? (auth()?->id() ?? 1)
        );
    }
}
