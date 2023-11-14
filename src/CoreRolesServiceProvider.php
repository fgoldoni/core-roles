<?php

namespace Goldoni\CoreRoles;

use Illuminate\Support\ServiceProvider;
use Goldoni\CoreRoles\Console\Commands\CoreRolesGenerate;
use Goldoni\CoreRoles\Console\Commands\CoreRolesInstall;

class CoreRolesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //Register Migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        //Register Config file
        $this->mergeConfigFrom(__DIR__.'/../config/core-roles.php', 'core-roles');

        //Publish Config file
        $this->publishes([
            __DIR__.'/../config/core-roles.php' => config_path('core-roles.php')
        ], 'core-roles-config');

        //Publish Migrations
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'core-roles-migrations');

        //Register generate command
        $this->commands([
            CoreRolesGenerate::class,
            CoreRolesInstall::class
        ]);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
