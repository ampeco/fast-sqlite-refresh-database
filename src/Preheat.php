<?php

namespace Ampeco\Modules\FastSqliteRefreshDatabase;

use Illuminate\Console\Command;

class Preheat extends Command
{
    protected $signature = 'fast-sqlite-refresh-database:preheat';

    protected $description = 'Preheat the database for fast-sqlite-refresh-database';

    public function handle(SqliteRefreshDatabaseService $sqliteRefreshDatabaseService)
    {
        $this->info('Preheating the sqlite database...');

        if (!file_exists(config('database.connections.sqlite.database'))) {
            touch(config('database.connections.sqlite.database'));
        }

        $sqliteRefreshDatabaseService->refreshDatabase(config('database.default'));

        $this->info('Database preheated!');
    }
}
