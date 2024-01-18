<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\MariadbTeamDatabase;
use App\Models\MysqlTeamDatabase;
use App\Models\SqliteTeamDatabase;
use App\Policies\TeamDatabasePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        MariadbTeamDatabase::class => TeamDatabasePolicy::class,
        SqliteTeamDatabase::class => TeamDatabasePolicy::class,
        MysqlTeamDatabase::class => TeamDatabasePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
