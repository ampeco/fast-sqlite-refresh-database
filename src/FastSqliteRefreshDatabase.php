<?php

namespace Ampeco\Modules\FastSqliteRefreshDatabase;

use Illuminate\Foundation\Testing\RefreshDatabase;

trait FastSqliteRefreshDatabase
{
    use RefreshDatabase;

    /**
     * Refresh a conventional test database.
     *
     * @throws \JsonException
     * @return void
     */
    protected function refreshTestDatabase()
    {
        $service = $this->app->make(SqliteRefreshDatabaseService::class);
        $name = config('database.default');
        $driver = config('database.connections.' . $name . '.driver');
        if ($driver === 'sqlite') {
            $service->refreshDatabase($name);
        }
    }
}
