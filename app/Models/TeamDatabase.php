<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TeamDatabase extends Model
{
    use HasChildren;
    use HasFactory;
    use InteractsWithSystemDatabase;
    use UsesLandlordConnection;
    use WithUuid;

    public $originalConfig = [];

    public $originalDefaultConnectionName = null;

    protected $childColumn = 'driver';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [
        //
    ];

    public function getChildTypes()
    {
        return TeamDatabaseType::toArray();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function teams()
    {
        return $this->hasMany(Team::class);
    }

    public static function boot(): void
    {
        parent::boot();
        static::creating(function (Model $model) {
            if (! $model->user_id) {
                $model->user_id = auth()->id() ?? 1;
            }
            // if not running tests
            if (! app()->runningUnitTests() && ! config('b2bsaas.database_creation_disabled')) {
                $model->createTeamDatabase()
                    ->migrate();
            }elseif (app()->runningUnitTests()) {
                $model->createTeamDatabase(testing: true)->migrate();
            }
        });
    }

    public function configure()
    {
        $this->prepareTenantConnection($connection = $this->createTenantConnection());

        return $this;
    }

    public function use()
    {
        app()->forgetInstance('teamDatabase');

        app()->instance('teamDatabase', $this);
    }

    public function migrate()
    {
        $this->configure()->handleMigration();

        $this->restoreOriginalConnection();

        return $this;
    }

    public function delete()
    {
        $this->deleteTeamDatabase();

        parent::delete();
    }

    protected function restoreOriginalConnection(): void
    {
        if (! empty($this->originalConfig)) {
            config()->set('database.connections.'.$this->originalDefaultConnectionName, $this->originalConfig);
            config()->set('database.default', $this->originalDefaultConnectionName);
        }
    }

    protected function createTenantConnection(): string
    {
        if(! app()->runningUnitTests()) {
            
            $driver = (string) str()->of($this->driver)->lower();
            
            $connectionTemplate = config('database.connections.tenant_'.$driver);
            
        }else{ 
            $connectionTemplate = config('database.connections.testing_tenant');
        }
        $databaseConfig = [];

        if (! config('b2bsaas.database_creation_disabled') && ! app()->runningUnitTests()) {
            $databaseConfig['database'] = $this->name;
        }

        config()->set('database.connections.'.$this->name, array_merge($connectionTemplate, $databaseConfig));

        return $this->name;
    }

    protected function prepareTenantConnection($name): void
    {
        $default = once(fn() => config('database.default'));

        $this->originalDefaultConnectionName = $default;

        $this->originalConfig = once(fn() => config('database.connections.'.$this->originalDefaultConnectionName));

        config()->set('database.default', $name);

        DB::purge();

        DB::reconnect();

        Schema::connection(config('database.default'))->getConnection()->reconnect();
    }
}
