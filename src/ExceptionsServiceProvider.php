<?php

namespace iMemento\Exceptions\Laravel;

use Illuminate\Support\ServiceProvider;

class ExceptionsServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->setupConfig();
    }

    /**
     * Register the application services.
     */
    public function register()
    {

    }

    protected function setupConfig()
    {
        $source = realpath(__DIR__.'/../resources/config/exceptions.php');

        $this->publishes([$source => config_path('exceptions.php')], 'config');

        $this->mergeConfigFrom($source, 'exceptions');
    }
}
