<?php

namespace App\Providers;

use App\Policies\TeamDatabasePolicy;
use App\UserType;
use B2bSaas\MariadbTeamDatabase;
use B2bSaas\MysqlTeamDatabase;
use B2bSaas\SqliteTeamDatabase;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
        Gate::before(function ($user) {
            if ($user->type === UserType::SuperAdmin->name) {
                return true;
            }
        });
    }
}
