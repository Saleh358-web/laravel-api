<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan as Artisan;

trait TestDatabaseTrait
{
    /**
     * Migrate and seed the database
     *
     * @return void
     */
    public function manageDatabase(): void
    {
        Artisan::call('config:clear');
        // Artisan::call('migrate:fresh --seed');
        // Artisan::call('passport:install');
        Artisan::call('config:clear');
    }
}
