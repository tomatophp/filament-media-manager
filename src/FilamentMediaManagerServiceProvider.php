<?php

namespace TomatoPHP\FilamentMediaManager;

use Illuminate\Support\ServiceProvider;
use TomatoPHP\FilamentMediaManager\Services\FilamentMediaManagerServices;


class FilamentMediaManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //Register generate command
        $this->commands([
           \TomatoPHP\FilamentMediaManager\Console\FilamentMediaManagerInstall::class,
        ]);

        //Register Config file
        $this->mergeConfigFrom(__DIR__.'/../config/filament-media-manager.php', 'filament-media-manager');

        //Publish Config
        $this->publishes([
           __DIR__.'/../config/filament-media-manager.php' => config_path('filament-media-manager.php'),
        ], 'filament-media-manager-config');

        //Register Migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        //Publish Migrations
        $this->publishes([
           __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'filament-media-manager-migrations');
        //Register views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'filament-media-manager');

        //Publish Views
        $this->publishes([
           __DIR__.'/../resources/views' => resource_path('views/vendor/filament-media-manager'),
        ], 'filament-media-manager-views');

        //Register Langs
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'filament-media-manager');

        //Publish Lang
        $this->publishes([
           __DIR__.'/../resources/lang' => base_path('lang/vendor/filament-media-manager'),
        ], 'filament-media-manager-lang');

        if(config('filament-media-manager.api.active')){
            //Register Routes
            $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        }

        $this->app->bind('filament-media-manager', function () {
            return new FilamentMediaManagerServices();
        });
    }

    public function boot(): void
    {
        //you boot methods here
    }
}
