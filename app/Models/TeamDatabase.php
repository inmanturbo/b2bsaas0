<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TeamDatabase extends Model
{
    use HasFactory;
    use UsesLandlordConnection;
    use WithUuid;
    use HasChildren;
    use InteractsWithSystemDatabase;

    public $originalConfig = [];

    protected $childColumn = 'driver';

    protected $tenantConnection = 'tenant_connection';

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
            if(!$model->user_id) {
                $model->user_id = auth()->id() ?? 1;
            }
            // if not running tests
            if (!app()->runningUnitTests() && !config('b2bsaas.database_creation_disabled')) {
                $model->createTeamDatabase()
                    ->migrate();
            }
        });
    }

    public function configure()
    {
        $this->prepareTenantConnection($this->name);

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

        if(!empty($this->originalConfig)) {
            config()->set('database.connections.' . $this->tenantConnection, $this->originalConfig);
        }

        return $this;
    }

    public function delete()
    {
        $this->deleteTeamDatabase();

        parent::delete();
    }

    protected function prepareTenantConnection($name)
    {
        $this->originalConfig = config('database.connections.' . $this->tenantConnection);

        config()->set('database.connections.' . $this->tenantConnection . '.database', $name);

        DB::purge($this->tenantConnection);

        DB::reconnect($this->tenantConnection);

        Schema::connection($this->tenantConnection)->getConnection()->reconnect();
    }
}
