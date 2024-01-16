<?php

namespace App\Models;

use B2bSaas\AddressData;
use B2bSaas\ContactData;
use B2bSaas\HasLandingPage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Laravel\Jetstream\Events\TeamCreated;
use Laravel\Jetstream\Events\TeamDeleted;
use Laravel\Jetstream\Events\TeamUpdated;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\Team as JetstreamTeam;

class Team extends JetstreamTeam
{
    use HasFactory;
    use HasLandingPage;
    use HasProfilePhoto;
    use UsesLandlordConnection;
    use WithUuid;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'personal_team' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The event map for the model.
     *
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'created' => TeamCreated::class,
        'updated' => TeamUpdated::class,
        'deleted' => TeamDeleted::class,
    ];

    public static function boot(): void
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

    public function url(): Attribute
    {
        return new Attribute(
            get: fn ($value, $attributes) => (isset($attributes['domain']) && ! empty($attributes['domain'])) ? $this->preferHttps($attributes['domain']).'asdfadsf' : $this->landingPageUrl,
        );
    }

    public function contactData(): Attribute
    {
        return new Attribute(
            get: fn ($value, $attributes) => $this->getContactData($value, $attributes),
            set: fn ($value, $attributes) => json_encode($value),
        );
    }

    public function getContactData($value, $attributes)
    {
        $companyData = json_decode($attributes['contact_data'] ?? '', true);

        $address = new AddressData(
            city: $companyData['address']['city'] ?? null,
            state: $companyData['address']['state'] ?? null,
            zip: $companyData['address']['zip'] ?? null,
            street: $companyData['address']['street'] ?? null,
            country: $companyData['address']['country'] ?? 'USA',
            lineTwo: $companyData['address']['lineTwo'] ?? null,
        );

        return new ContactData(
            name: $companyData['name'] ?? $this->name,
            landingPage: $companyData['landingPage'] ?? null,
            address: $address,
            logoUrl: $this->profile_photo_url,
            logoPath: $this->profile_photo_path ?? config('b2bsaas.company.empty_logo_path'),
            phone: $companyData['phone'] ?? config('b2bsaas.company.empty_phone'),
            fax: $companyData['fax'] ?? config('b2bsaas.company.empty_phone'),
            email: $companyData['email'] ?? null,
            website: $companyData['website'] ?? null,
        );
    }

    public function configure()
    {
        config([
            'cache.prefix' => $this->id,
            'b2bsaas.company.logo_path' => $this->contact_data?->logoPath(),
            'b2bsaas.company.name' => $this->contact_data?->name(),
            'b2bsaas.company.phone' => $this->contact_data?->phone(),
            'b2bsaas.company.fax' => $this->contact_data?->fax(),
            'b2bsaas.company.street_address' => $this->contact_data?->streetAddress(),
            'b2bsaas.company.city_state_zip' => $this->contact_data?->cityStateZip(),
            'b2bsaas.company.email' => $this->contact_data?->email(),
        ]);

        // if not running unit tests
        if (! app()->runningUnitTests()) {
            $this->teamDatabase->configure();
        }

        app('cache')->purge(
            config('cache.default')
        );

        return $this;
    }

    public function use()
    {
        $this->teamDatabase->use();

        app()->forgetInstance('team');

        app()->instance('team', $this);

        app()->forgetInstance('contact');

        app()->instance('contact', $this->contact_data);

        return $this;
    }

    public function purge()
    {
        parent::purge();

        if (isset(app()['team']) && app()['team']->uuid === $this->uuid) {
            app()->forgetInstance('team');
            if (request()->user()->teams->count() > 0) {
                //switch to the first team
                request()->user()->switchTeam(request()->user()->teams?->first());
                request()->user()->teams?->first()?->use();
            }
        }
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

    //use  native laravel http client to check if domain supports https
    public function preferHttps($domain)
    {
        try {
            $response = Http::get("https://{$domain}");
            if ($response && $response->status() === 200) {
                return "https://{$domain}";
            }
        } catch (\Exception $e) {
            //do nothing
        }

        return "http://{$domain}";
    }
}
