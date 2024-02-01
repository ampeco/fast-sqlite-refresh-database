<?php

namespace Ampeco\Modules\FastSqliteRefreshDatabase;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Preheat::class,
            ]);
        }

    }

    public function register()
    {
    }
}
