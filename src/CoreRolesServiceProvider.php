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

    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
