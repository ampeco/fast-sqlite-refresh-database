<?php

namespace Ampeco\Modules\FastSqliteRefreshDatabase;

use App\Console\Kernel;
use Illuminate\Support\Facades\DB;

class SqliteRefreshDatabaseService
{
    public function __construct(protected \Illuminate\Foundation\Application $app)
    {
    }

    protected function getTemplatePath(string $name)
    {
        $databaseFile = config('database.connections.' . $name . '.database');
        if ($databaseFile === ':memory:') {
            return null;
        }

        return dirname($databaseFile) . '/' . $name . '.template.sqlite';
    }

    public function refreshDatabase(string $name)
    {
        $templatePath = $this->getTemplatePath($name);
        if ($templatePath === null) {
            return;
        }
        $databasePath = config('database.connections.' . $name . '.database');
        if (file_exists($templatePath) && $this->shouldRestoreTemplate($databasePath, $templatePath)) {
            $this->restoreTemplate($databasePath, $templatePath, $name);
        }

        if (env('PREHEATED', false)) {
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

        $this->app[Kernel::class]->setArtisan(null);
    }

    private function migrate(string $name)
    {
        $this->app[Kernel::class]->call('migrate', [
            '--database' => $name,
        ]);
        $this->app[Kernel::class]->setArtisan(null);
    }

    private function shouldSaveTemplate(string $databaseFile, $templateFile)
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
        if (!file_exists($databaseFile)) {
            return true;
        }
        $templateHash = md5_file($templateFile);
        $databaseHash = md5_file($databaseFile);

        return $templateHash !== $databaseHash;
    }
}
