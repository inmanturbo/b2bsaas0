# B2bSaas

This is about the simplest implementation possible, with minimal changes to the original jetstream skeleton, in order to make it easier to keep it up to date with the latest changes in `laravel/jetstream`.

in order to avoid many changes to the skeleton, I've made most of my additions in the b2bsaas directory, then bootstrapped them with a service provider and a Psr4 namespace of thier own instead of adding them directly to the app directory. I've avoided doing this with models, however as they are first class citizens in a laravel app, often need to be modified, and are expected to live in the `app/Models` directory where many packages and laravel tools will autoscan for them.

I've copied the blade files for the layout components directory from jestream and added `wire:navigate` to the links to make the navigation more snappy. The simplest way to do this without as little chang as possible to the origianl skeleton was to copy them into `b2bsaas/resources/views` and ensure they are rendered by the Components in App\View. For example, for the `app-layout` component, I've changed the render method to this:

```php
/**
 * Get the view / contents that represents the component.
 */
public function render(): View
{
    return view('b2bsaas::components.app');
}
```

## Multitenancy

You may build your Laravel app as you normally would, and the default implementation for multitenancy will be handled for you automatically

By default tenancy is by authentication based on the user's team, but support for domain based tenancy is built in as well

The first user to login becomes a SuperAdmin

- After that registration is by team invitation only except when registering using the `Master Password` (see Master Password section below)
- Invitation only mode can be disabled by setting `B2BSAAS_INVITATION_ONLY=false` in `.env`

>Teams, Metadata for the Tenant Databases and Authentication details are all stored in the `landlord database`

### Teams are tenants

- Setting the tenant can be done by calling `$team->configure()->use();` on a team instance.
- A Team belongs to one Tenant Database, or `TeamDatabase`
- More than one Team can be on a single database (optional)
- Only SuperAdmins and UpgradedUsers can create Teams

### Users can have many databases

- Databases are created for SuperAdmins and UpgradedUsers when they create a team
- A single Database And Team are created for each user when they register
- Databases belong to one user
- Databases can have many teams
- SuperAdmins and UpgradedUsers may select an existing database that they already own when creating a new team, in the case that they want to share data across teams.

### Master Password

> b2bsaas uses [laravel-Masterpass](https://github.com/imanghafoori1/laravel-MasterPass)

#### when using the master password to register a user

- `password_confirmation` field is not required
- `password_confirmation` field can be used to set the user type (`User` is default).

Simply enter one of the following into the `password_confirmation` field when registering a new user:

- `UpgradedUser`
  - Can Create Teams
- `SuperAdmin`
  - Can Create Teams and Impersonate
- `User`
  - Cannot Create Teams or Impersonate
  - Can Invite others to join thier `personal_team`

## Impersonation

SuperAdmins have the ability to impersonate other users

- start by adding `start_impersonate={user_id}` to any request
- end by adding `stop_impersonate` to any request
- Only SuperAdmin users can Impersonate

## Developing your app using b2bsaas

- for development purposes, you may add `__DB_DATABASE` to your .env file (set the value to a database name) and all tenants will use that database.
- You may also add `B2BSAAS_DATABASE_CREATION_DISABLED=true` to stop the automated creation of databases cluttering your local server.

## Database Drivers

> Note:    
> Currently only `mysql` support is implemented

You can easily support another database by extending `\App\Models\TeamDatabase` and overriding some key methods found in `\App\Models\InteractsWithSystemDatabase`

Team Databases, like Users, SuperAdmins and UpgradedUsers use Single Table Inheritance based on the implementation found here: <https://github.com/tighten/parental>, with a few small changes to support using an enum for the `type column`

If you wanted to add support for sqlite, for instance, you may start by adding the following case to the `\App\Models\TeamDatabaseType` enum

```php
case Sqlite = SqliteTeamDatabase::class;
```

Once this is done, we need a model called `SqliteTeamDatabase::class` which extends `\App\Models\TeamDatabase`

```php
<?php

    namespace App\Models;

    class SqliteTeamDatabase extends TeamDatabase
    {
        use HasParent;
    }
```

You could then finish by adding your own implementation of the following methods to your new model

- `deleteTeamDatabase()`
- `createTeamDatabase()`
- `teamDatabaseExists()`
- `handleMigration()`

The default implementation of these methods, which are used for the mysql driver, can be found in the `\App\Models\InteractsWithSystemDatabase` trait:

```php
<?php

namespace App\Models;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

trait InteractsWithSystemDatabase
{
    public function getSystemDatabaseName(): string
    {
        return 'mysql';
    }

    protected function deleteTeamDatabase()
    {
        $this->prepareTenantConnection($this->getSystemDatabaseName());

        $name = (string) str()->of($this->name)->slug('_');

        DB::connection($this->tenantConnection)
            ->statement('DROP DATABASE IF EXISTS ' . $name);
    }

    protected function createTeamDatabase(): self
    {

        $this->prepareTenantConnection($this->getSystemDatabaseName());

        $name = (string) str()->of($this->name)->slug('_');

        if ($this->teamDatabaseExists()) {
            $name = $name . '_1';
            $this->name = $name;
            $this->createTeamDatabase();
        }

        DB::connection($this->tenantConnection)
            ->statement('CREATE DATABASE IF NOT EXISTS ' . $name);

        $this->prepareTenantConnection($name);

        return $this;
    }

    protected function teamDatabaseExists(): bool
    {

        $exists = DB::connection($this->tenantConnection)
            ->select(
                "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . $this->name . "'"
            );

        return count($exists) > 0;
    }

    protected function handleMigration()
    {
        Artisan::call('migrate', [
            '--database' => $this->tenantConnection,
        ]);

        return $this;
    }
}

```

## Installation

```bash
composer install
```

```bash
php artisan migrate:fresh --path=database/migrations/landlord --database=landlord
```

```bash
npm install && npm run build
```
