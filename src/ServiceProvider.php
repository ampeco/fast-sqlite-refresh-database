<?php

namespace Ampeco\Modules\FastSqliteRefreshDatabase;

//use Laravel\Nova\Nova;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Preheat::class,
            ]);
        }

//        $this->loadMigrationsFrom(dirname(__DIR__) . '/migrations');
//        $this->publishes([
//            dirname(__DIR__) . '/config/config.publish.php' => config_path('MODULENAME.php'),
//        ]);
//
//        Nova::serving(function () {
//            Nova::resources([
//                // Module-provided resources
//            ]);
//        });

//        $this->loadViewsFrom(dirname(__DIR__) . '/views', 'MODULENAME');
//        $this->loadTranslationsFrom(dirname(__DIR__) . '/translations', 'MODULENAME');

//        if (($this->app->isLocal() || $this->app->environment() === 'testing' || $this->app->environment() === 'develop') && !env('_TESTING_PRODUCTION_COMMANDS')) {
//            $this->app->make('Illuminate\Database\Eloquent\Factory')->load(__DIR__ . '/../factories');
//        }
    }

    public function register()
    {
//        $this->mergeConfigFrom(
//            dirname(__DIR__) . '/config/config.default.php', 'MODULENAME',
//        );
    }
}
