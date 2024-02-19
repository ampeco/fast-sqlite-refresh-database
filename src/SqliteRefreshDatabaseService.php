<?php

namespace Ampeco\Modules\FastSqliteRefreshDatabase;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class SqliteRefreshDatabaseService
{
    public function __construct(protected \Illuminate\Foundation\Application $app)
    {
    }

    protected function getTemplatePath(string $databasePath, string $name): ?string
    {
        if ($databasePath === ':memory:') {
            return null;
        }

        return dirname($databasePath) . '/' . $name . '.template.sqlite';
    }

    protected function getDatabasePath(string $name): ?string
    {
        return config('database.connections.' . $name . '.database');
    }

    public function refreshDatabase(string $name): void
    {
        \Log::debug('[FAST] Refreshing database');

        $databasePath = $this->getDatabasePath($name);
        if ($databasePath === null) {
            return;
        }
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

    private function restoreTemplate(string $databaseFile, string $templateFile, string $name): void
    {
        DB::disconnect($name);

        if (!copy($templateFile, $databaseFile)) {
            \Log::error('[FAST] Unable to restore template');
        }
    }

    private function migrate(string $name): void
    {
        Artisan::call('migrate', [
            '--database' => $name,
        ]);
    }

    private function shouldSaveTemplate(string $databaseFile, string $templateFile): bool
    {
        if (!file_exists($templateFile)) {
            return true;
        }

        if (filesize($templateFile) !== filesize($databaseFile)) {
            return true;
        }

        $templateVersion = $this->getDatabaseChangeCounter($templateFile);
        $databaseVersion = $this->getDatabaseChangeCounter($databaseFile);

        return $templateVersion !== $databaseVersion;
    }

    private function saveTemplate(string $name, string $databaseFile, string $templatePath): void
    {
        DB::disconnect($name);
        copy($databaseFile, $templatePath);
    }

    private function shouldRestoreTemplate(string $databasePath, string $templatePath): bool
    {
        // We should not restore from a template if the database file does not exist
        if (!file_exists($templatePath)) {
            return false;
        }

        return $this->sqliteDatabaseDiffer($databasePath, $templatePath);
    }

    private function sqliteDatabaseDiffer(string $file1, string $file2): bool
    {
        if (!file_exists($file1) || !file_exists($file2)) {
            return true;
        }
        if (filesize($file1) !== filesize($file2)) {
            return true;
        }

        $counter1 = $this->getDatabaseChangeCounter($file1);

        // Unable to read the sqlite database change counter
        if ($counter1 === null) {
            return true;
        }

        return $counter1 !== $this->getDatabaseChangeCounter($file2);
    }

    /**
     * @param $file
     * @return int|null the change counter or null if the file does not exist or unable to read the counter
     */
    private function getDatabaseChangeCounter($file): ?int
    {
        if (!file_exists($file)) {
            return null;
        }
        $handle = fopen($file, 'rb');
        fseek($handle, 24);
        $data = fread($handle, 4);
        $counter = unpack('V', $data)[1];  // interpret the data as little-endian unsigned long
        fclose($handle);

        return $counter ?? null;
    }
}
