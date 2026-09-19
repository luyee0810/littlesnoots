<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Shared hosting (cPanel) often defaults to MyISAM with a 1000-byte index
        // limit, where utf8mb4 VARCHAR(255) unique keys (255 * 4 = 1020 bytes) fail.
        // 191 * 4 = 764 bytes fits, and also clears InnoDB's older 767-byte limit.
        Schema::defaultStringLength(191);
    }
}
