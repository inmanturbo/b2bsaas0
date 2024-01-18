# B2bSaas
- [Introduction](#introduction)
- [Multitenancy](#multitenancy)
  - [Teams Are Tenants](#teams-are-tenants) 
  - [Users Can Have Many Databases](#users-can-have-many-databases)
  - [Master Password](#master-password)
    - [Using the Master Password to register a user](#when-using-the-master-password-to-register-a-user)
- [Impersonation](#impersonation)
- [Usage During Development](#usage-during-development)
- [Database Types](#database-types)
- [Configuration](#configuration)
- [Installation](#installation)



## Introduction
This is about the simplest implementation possible, with minimal changes to the original jetstream skeleton, to to make it easier to keep it up to date with the latest changes in `laravel/jetstream`.

In order to avoid many changes to the skeleton, I've made most of my additions in the `b2bsaas/` directory, then bootstrapped them with a service provider and a Psr4 namespace of their own instead of adding them directly to the app directory. I've avoided doing this with models however, as they are first class citizens in a laravel app, often need to be modified, and are expected to live in the `app/Models` directory where many packages and laravel tools will autoscan for them.

> NOTE:    
> b2bsaas IS NOT a package under vendor/, or using any fancy "modules" package --    
> It is simply using a custom namespace (B2bSaas) 
> added to composer.json in the PSR4 Autoload section,    
> in addition to the standard App root.    
> Feel free to modify it as needed!

I've copied the blade files for the layout components directory from jetstream and added `wire:navigate` to the links to make the navigation more snappy. The simplest way to do this with as little change as possible to the original skeleton was to copy them into `b2bsaas/resources/views` and ensure they are rendered by the Components in `app/View`. For example, for the `app/View/AppLayout.php` component (tagged `<x-app-layout>...</x-app-layout>`), I've changed the render method to this:

```php
/**
 * Get the view / contents that represents the component.
 */
public function render(): View
{
    return view('b2bsaas::components.app');
}
```
The markup for the view `b2bsaas::components.app` lives in `b2bsaas/resources/views/components/app.blade.php`.

## Multitenancy

You may build your Laravel app as you normally would, and the default implementation for multitenancy will be handled for you automatically

By default tenancy is by authentication based on the user's team, but support for domain based tenancy is built in as well

The first user to login becomes a SuperAdmin

- After that registration is by team invitation only except when registering using the `Master Password` (see Master Password section below)
- Invitation only mode can be disabled by setting `B2BSAAS_INVITATION_ONLY=false` in `.env`

>Teams, Metadata for the Tenant Databases and Authentication details are all stored in the `landlord database`

### Teams are tenants

- Setting the tenant can be done by calling `$team->configure()->use();` on a team instance. This is done automatically when a user logs in, or when a request is for a domain registered to a team.
  - Authentication Based tenancy 
    - [Team Auth Trait](https://github.com/inmanturbo/b2bsaas/blob/main/b2bsaas/src/Http/Middleware/HandlesTeamAuth.php#L39C13-L39C13)
    - [Team Middleware](https://github.com/inmanturbo/b2bsaas/blob/main/b2bsaas/src/Http/Middleware/TeamMiddleware.php#L36)
  - Domain Based Tenancy
    - [Configure Requests](https://github.com/inmanturbo/b2bsaas/blob/main/b2bsaas/src/B2bSaasServiceProvider.php#L63) 

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

## Usage during development

- for development purposes, you may add `__DB_DATABASE` to your .env file (set the value to a database name) and all tenants will use that database.
- You may also add `B2BSAAS_DATABASE_CREATION_DISABLED=true` to stop the automated creation of databases cluttering your local server.

## Database Types

> Note:    
> Currently only `mysql`, `mariadb` and `sqlite`, support are currently implemented for tenant databases

You can easily support another database by extending `\App\Models\TeamDatabase` and overriding some key methods found in `\B2bSaas\InteractsWithSystemDatabase`

Team Databases, like Users, SuperAdmins and UpgradedUsers use Single Table Inheritance based on the implementation found here: <https://github.com/tighten/parental>, with a few small changes to support using an enum for the `type column`

The `team_databases` table has a `connection_templat`e column, the value of which should be the name of a `\App\Models\TeamDatabaseType` that references a model.

This name must also be the name of a database connection which holds the configuration details for the database connection. Example:

The type:

```php
...
enum TeamDatabaseType: string
{
    ...
    case tenant_sqlite = Models\SqliteTeamDatabase::class;
    ...
}
```

The Model:

```php
<?php

namespace App\Models;

use Artisan;
use B2bSaas\HasParent;
use Illuminate\Support\Facades\Storage;

class SqliteTeamDatabase extends TeamDatabase
{
    use HasParent;

    protected function createTeamDatabase(bool $testing = false): self
    {
        $name = (string) str()->of($this->name)->slug('_');

        if ($this->teamDatabaseExists(testing: $testing)) {
            $name = $name.'_1';
            $this->name = $name;
            $this->createTeamDatabase(testing: $testing);
        }

        $userUuid = (string) $this->user->uuid;

        // create storage directory for user if it doesn't exist
        if (! file_exists(storage_path('app/'.$userUuid))) {
            mkdir(storage_path('app/'.$userUuid));
        }

        if (! file_exists(storage_path('app/'.$userUuid.'/'.$name.'.sqlite'))) {
            Storage::disk('local')->put($userUuid.'/'.$name.'.sqlite', '');
        }

        return $this;
    }

...
}
```

The `connection_template`:

```php
// config/database.php
...
  'connections' => [

...
        'tenant_sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DATABASE_URL'),
            'database' => env('__DB_DATABASE'),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],
...
  ]
```

The above "connection_template" (`tenant_sqlite`) will be merged along with the tenant specific details to create a working connection for the database at runtime.

Note that the connection name `tenant_sqlite` is the same as the name for the enum case. `tenant_sqlite` is also the value that will be stored in the `connection_template` column for any `App\Models\SqliteTeamDatabase` instances.

## Configuration

### Config file

The path to the b2bsaas config file is `b2bsaas/src/config/b2bsaas.php`. However most of the options can be set using environment variables in your `.env` file.

#### APP_URL_SCHEME

The url scheme for the application can be set by setting `APP_URL_SCHEME`. Default is `http`. Options are `http` and `https`.    
Setting `APP_URL_SCHEME=https` in your `.env` will force all app urls to use `https` protocol.

#### DEFAULT_TEAM_DATABASE_CONNECTION_TEMPLATE

Setting the `DEFAULT_TEAM_DATABASE_CONNECTION_TEMPLATE` in your `.env` to a value corresponding to a database connection will cause the application to use that connection as a template for tenant database configuration.    
This value must also match a case name in `app/Models/TeamDatabaseType.php`, the value of which should be a model which extends `App\Models\TeamDatabase` and uses the `B2bSaas\HasParent` trait.
The available values can be found by inspecting the `app/Models/TeamDatabaseType.php` file. At the time of writing this, "out of the box" options include `tenant_mysql`, `tenant_mariadb` and `tenant_sqlite`.

For example, setting `DEFAULT_TEAM_DATABASE_CONNECTION_TEMPLATE=tenant_sqlite` in your `.env` file will cause the application to create and use sqlite databases for tenants by default. `tenant_sqlite` corresponds to the following `TeamDatabaseType`: 

```php
case tenant_sqlite = Models\SqliteTeamDatabase::class;
```

This means that the names of these tenant databases will be stored in the `team_databases` table of the landlord database. The `connection_template` column on these instances will be set to `tenant_sqlite`, which is how the application knows to use the `SqliteTeamDatabase` model for these instances. Also, `tenant_sqlite` is a database connection in `config/database.php` which will be used to build the connection config for these `TeamDatabase` instances. By default sqlite databases live in the `storage/app` directory, under a folder by the uuid of the user who owns the database. The `SqliteTeamDatabase` class holds the logic for how these databases are created, in a method called `createTeamDatabase`, which is called by its parent class `TeamDatabase` during boot whenever a new instance is created.

## Installation

```bash
cp .env.example .env
```

```bash
mysql -u root -e "create database b2bsaas_landlord"
```

```bash
composer install
```

```bash
# landlord_mysql is the default landlord connection
php artisan migrate:fresh --path=database/migrations/landlord --database=landlord_mysql
```

```bash
npm install && npm run build
```

## Migrating tenant databases

```bash
php artisan teams:migrate --help
```

```bash
Description:
  Migrate the database for the specified team database, or all team databases if none is specified.

Usage:
  teams:migrate [options] [--] [<teamDatabaseName>]

Arguments:
  teamDatabaseName      

Options:
      --fresh           Wipe the database(s)
      --seed            Seed the database(s)
      --force           Force the operation(s) to run when in production
      --pretend         Dump the SQL queries that would be run
      --path[=PATH]     The path of migrations files to be executed
      --realpath        Indicate any provided migration file paths are pre-resolved absolute paths
      --step            Force the migrations to be run so they can be rolled back individually
      --rollback        Rollback the last database migration
  -h, --help            Display help for the given command. When no command is given display help for the list command
  -q, --quiet           Do not output any message
```
