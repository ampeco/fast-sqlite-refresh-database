<?php

namespace Ampeco\Modules\FastSqliteRefreshDatabase;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class SqliteRefreshDatabaseService
{
    public function __construct(protected \Illuminate\Foundation\Application $app)
    {
    }

    protected function getTemplatePath(string $databasePath, string $name)
    {

        if ($databasePath === ':memory:') {
            return null;
        }

        return dirname($databasePath) . '/' . $name . '.template.sqlite';
    }

    protected function getDatabasePath(string $name){
        return config('database.connections.' . $name . '.database');
    }

    public function refreshDatabase(string $name)
    {
        $databasePath = $this->getDatabasePath($name);
        $templatePath = $this->getTemplatePath($databasePath, $name);
        if ($templatePath === null) {
            return;
        }
        if ($this->shouldRestoreTemplate($databasePath, $templatePath)) {
            $this->restoreTemplate($databasePath, $templatePath, $name);
        }

        if (env('FAST_SQLITE_REFRESH_DATABASE_PREHEATED', false)) {
            return;
        }
        $this->migrate($name);

        if ($this->shouldSaveTemplate($databasePath, $templatePath)) {
            $this->saveTemplate($name, $databasePath, $templatePath);
        }
    }

    private function restoreTemplate(string $databaseFile, string $templateFile, string $name)
    {
        DB::disconnect($name);

        if (file_exists($databaseFile)) {
            unlink($databaseFile);
        }
        copy($templateFile, $databaseFile);
    }

    private function migrate(string $name)
    {
        Artisan::call('migrate', [
            '--database' => $name,
        ]);
    }

    private function shouldSaveTemplate(string $databaseFile, string $templateFile)
    {
        if ($templateFile === null) {
            return false;
        }
        if (!file_exists($templateFile)) {
            return true;
        }
        $templateHash = md5_file($templateFile);
        $databaseHash = md5_file($databaseFile);

        return $templateHash !== $databaseHash;
    }

    private function saveTemplate(string $name, string $databaseFile, string $templatePath)
    {
        DB::disconnect($name);
        copy($databaseFile, $templatePath);
    }

    private function shouldRestoreTemplate(string $databaseFile, $templateFile)
    {
        if (!file_exists($templateFile)) {
            return false;
        }
        if (!file_exists($databaseFile)) {
            return true;
        }
        $templateHash = md5_file($templateFile);
        $databaseHash = md5_file($databaseFile);

        return $templateHash !== $databaseHash;
    }
}
