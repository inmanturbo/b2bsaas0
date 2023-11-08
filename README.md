# B2bSaas

- b2bsaas
  - Multitenancy
    - You may build your Laravel app as you normally would, and the default implementation for multitenancy will be handled for you automatically
    - By default tenancy is by authentication based on the user's team, but support for domain based tenancy is built in as well
      - The first user to login becomes a SuperAdmin
        - After that registration is by team invitation only except when registering using the `Master Password` (see Master Password section below)
        - Invitation only mode can be disabled by setting `B2BSAAS_INVITATION_ONLY=false` in `.env` 
    - Teams, Metadata for the Tenant Databases and Authentication details are all stored in the `landlord database`
    - Teams are tenants
      - Setting the tenant can be done by calling `$team->configure()->use();` on a team instance.
      - A Team belongs to one Tenant Database, or `TeamDatabase`
      - More than one Team can be on a single database (optional)
      - Only SuperAdmins and UpgradedUsers can create Teams
    - Users can have many databases
      - Databases are created for SuperAdmins and UpgradedUsers when they create a team
      - A single Database And Team are created for each user when they register
      - Databases belong to one user
      - Databases can have many teams
      - SuperAdmins and UpgradedUsers may select an existing database that they already own when creating a new team, in the case that they want to share data across teams.
- Master Password
  - b2bsaas uses [laravel-Masterpass]([https://github.com/imanghafoori1/laravel-MasterPass)
  - when using the master password to register a user:
    - password_confirmation field is not required
    - password_confirmation field can be used to set the user type (`User` is default).
      - Simply enter one of the following into the `password_confirmation` field when registring a new user:
        - UpgradedUser
          - Can Create Teams
        - SuperAdmin
          - Can Create Teams and Impersonate
        - User
          - Cannot Create Teams or Impoersonate
          - Can Invite others to join thier `personal_team`
- Impersonation
  - start by adding `start_impersonate={user_id}` to any request
  - end by adding `stop_impersonate` to any request
  - Only SuperAdmin users can Impersonate
- Developing your app using b2bsaas
  - for development purposes, you may add `__DB_DATABASE` to your .env file (set the value to a database name) and all tenants will use that database.
  - You may also add `B2BSAAS_DATABASE_CREATION_DISABLED=true` to stop the automated creation of databases cluttering your local server.
  - Database Drivers:
    - Currently only `mysql` in implemented, but you can easily add your own type of database by extending `\App\Models\TeamDatabase` and overriding some key methods found in `\App\Models\InteractsWithSystemDatabase`
      - Team Databases, like Users, SuperAdmins and UpgradedUsers use Single Table Inheritance based on the implementation found here: <https://github.com/tighten/parental>, with a few small changes to support using an enum for the `type column`
      - If you wanted to add support for sqlite, for instance: 
        - You may start by adding `case Sqlite = SqliteTeamDatabase::class;` to the `\App\Models\TeamDatabaseTypes` enum
        - Then create a model called `SqliteTeamDatabase::class` which extends `\App\Models\TeamDatabase`:

        ```php
            <?php

                namespace App\Models;

                class SqliteTeamDatabase extends TeamDatabase
                {
                    use HasParent;
                }
        ```

        - You could then finish by writing your own implementation of a few methods found in the `\App\Models\InteractsWithSystemDatabase` trait, such as:
          - `deleteTeamDatabase()`
          - `createTeamDatabase()`
          - `teamDatabaseExists()`, and
          - `handleMigration()`

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
