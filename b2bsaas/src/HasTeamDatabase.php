<?php

namespace B2bSaas;

use App\Models;
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
        $column = Schema::connection($this->getConnectionName())->getConnection()->getDoctrineColumn('team_databases', 'connection_template');
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
        return $this->belongsTo(Models\TeamDatabase::class);
    }

    protected function createTeamDatabase(TeamDatabaseType $connectionTemplate = null): Models\TeamDatabase
    {

        if (! $connectionTemplate) {

            $defaultDriverName = $this->getDefaultTeamDatabaseDriverName();

            $connectionTemplate = $this->teamDatabaseDriver
                ? constant(TeamDatabaseType::class.'::'.$this->teamDatabaseDriver)
                : constant(TeamDatabaseType::class.'::'.$defaultDriverName);
        }

        return $connectionTemplate->createTeamDatabase(
            name: $this->name,
            userId: $this?->user_id ?? (auth()?->id() ?? 1)
        );
    }
}
