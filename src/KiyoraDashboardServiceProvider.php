<?php

namespace KiyoraDashboard;

use Illuminate\Support\ServiceProvider;

class KiyoraDashboardServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/crud.php' => config_path('crud.php'),
        ]);
        $this->publishes([
            __DIR__ . '/../config/template.php' => config_path('template.php'),
        ]);

        $this->publishes([
            __DIR__ . '/../database/' => base_path('database/migrations/'),
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->commands(
            'KiyoraDashboard\Commands\DeleteCrud',
            'KiyoraDashboard\Commands\MakeCrud',
        );
    }
}
