<?php

namespace Goldoni\CoreRoles;

use Illuminate\Support\ServiceProvider;
use Goldoni\CoreRoles\Console\Commands\CoreRolesGenerate;
use Goldoni\CoreRoles\Console\Commands\CoreRolesInstall;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

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
        $this->app['router']->aliasMiddleware('role', RoleMiddleware::class);
        $this->app['router']->aliasMiddleware('permission', PermissionMiddleware::class);
        $this->app['router']->aliasMiddleware('role_or_permission', RoleOrPermissionMiddleware::class);
    }
}
