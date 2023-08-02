<?php

namespace Mdhesari\LaravelAssistant;

use Illuminate\Support\ServiceProvider;
use Mdhesari\LaravelAssistant\Commands\CrudGeneratorCommand;
use Mdhesari\LaravelAssistant\Commands\MakeControllerCommand;
use Mdhesari\LaravelAssistant\Commands\MakeMigrationCommand;
use Mdhesari\LaravelAssistant\Commands\MakeModelCommand;
use Mdhesari\LaravelAssistant\Commands\MakeRequestCommand;

class LaravelAssistantServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'laravel-assistant');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-assistant');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('laravel-assistant.php'),
            ], 'config');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-assistant'),
            ], 'views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/laravel-assistant'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/laravel-assistant'),
            ], 'lang');*/

            // Registering package commands.
            $this->commands([
                CrudGeneratorCommand::class,
                MakeMigrationCommand::class,
                MakeRequestCommand::class,
                MakeControllerCommand::class,
                MakeModelCommand::class,
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'laravel-assistant');

        // Register the main class to use with the facade
        $this->app->singleton('laravel-assistant', function () {
            return new LaravelAssistant;
        });
    }
}
