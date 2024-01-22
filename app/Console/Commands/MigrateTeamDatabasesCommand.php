<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Inmanturbo\B2bSaas\TeamDatabase;

class MigrateTeamDatabasesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'teams:migrate 
                                {teamDatabaseName?} 
                                {--fresh : Wipe the database(s)} 
                                {--seed : Seed the database(s)} 
                                {--force : Force the operation(s) to run when in production} 
                                {--pretend : Dump the SQL queries that would be run}
                                {--path= : The path of migrations files to be executed}
                                {--realpath : Indicate any provided migration file paths are pre-resolved absolute paths}
                                {--step : Force the migrations to be run so they can be rolled back individually}
                                {--rollback : Rollback the last database migration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate the database for the specified team database, or all team databases if none is specified.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->argument('teamDatabaseName')) {
            $this->migrate(
                $database = TeamDatabase::whereName($this->argument('teamDatabaseName'))->firstOrFail()
            );
        } else {
            TeamDatabase::all()->each(
                fn ($db) => $this->migrate($db)
            );
        }
    }

    protected function migrate($db)
    {

        $this->line('');
        $this->line('-----------------------------------------');
        $this->line("Migrating team database #{$db->id} ({$db->name})");
        $this->line('-----------------------------------------');
        $this->line('');

        $options = [];

        if ($this->option('seed')) {
            $options['--seed'] = true;
        }

        if ($this->option('force')) {
            $options['--force'] = true;
        }

        if ($this->option('pretend')) {
            $options['--pretend'] = true;
        }

        if ($this->option('path')) {
            $options['--path'] = $this->option('path');
        }

        if ($this->option('realpath')) {
            $options['--realpath'] = true;
        }

        if ($this->option('step')) {
            $options['--step'] = true;
        }

        $db->configure();

        if ($this->option('rollback')) {
            $this->call(
                'migrate:rollback',
                $options
            );
        } elseif ($this->option('fresh')) {
            $this->call(
                'migrate:fresh',
                $options
            );
        } else {
            $this->call(
                'migrate',
                $options
            );
        }
    }
}
