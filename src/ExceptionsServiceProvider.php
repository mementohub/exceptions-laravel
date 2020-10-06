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
        $source = realpath(__DIR__ . '/../resources/config/exceptions.php');

        $this->publishes([$source => config_path('exceptions.php')], 'config');
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $source = realpath(__DIR__ . '/../resources/config/exceptions.php');

        $this->mergeConfigFrom($source, 'exceptions');
    }
}
