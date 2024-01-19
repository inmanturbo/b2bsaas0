<?php

namespace B2bSaas;

use App\Models\Team;
use App\Models\User;
use App\TeamDatabaseType;
use App\WithUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;

class TeamDatabase extends Model
{
    use HasChildren;
    use HasFactory;
    use InteractsWithSystemDatabase;
    use UsesLandlordConnection;
    use WithUuid;

    protected $childColumn = 'connection_template';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

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
        static::created(function (TeamDatabase $model) {
            if (! $model->user_id) {
                $model->user_id = auth()->id() ?? 1;
            }
            // if not running tests
            if (! app()->runningUnitTests() && ! config('b2bsaas.database_creation_disabled')) {
                $model->createTeamDatabase()
                    ->migrate();

            } elseif (app()->runningUnitTests()) {
                $model->createTeamDatabase(testing: true)->migrate();
            }
        });
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

    protected function handleMigration()
    {
        Artisan::call('migrate', [
            '--force' => true,
        ]);

        return $this;
    }
}
