<?php

namespace App\Providers;

use App\Policies\TeamDatabasePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Inmanturbo\B2bSaas\MariadbTeamDatabase;
use Inmanturbo\B2bSaas\MysqlTeamDatabase;
use Inmanturbo\B2bSaas\SqliteTeamDatabase;
use App\UserType;
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
