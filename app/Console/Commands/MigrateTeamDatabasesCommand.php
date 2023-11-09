<?php

namespace App\Console\Commands;

use App\Models\TeamDatabase;
use Illuminate\Console\Command;

class MigrateTeamDatabasesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'teams:migrate {teamDatabaseName?} {--fresh : Wipe the database} {--seed : Seed the database} {--force : Force the operation to run when in production}';

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

        $options = ['--database' => 'tenant_connection'];

        if ($this->option('seed')) {
            $options['--seed'] = true;
        }

        if ($this->option('force')) {
            $options['--force'] = true;
        }

        $db->configure();

        $this->call(
            $this->option('fresh') ? 'migrate:fresh' : 'migrate',
            $options
        );
    }
}
