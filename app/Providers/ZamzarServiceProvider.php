<?php

namespace App\Providers;

use App\Services\ZamzarService;
use Illuminate\Support\ServiceProvider;

class ZamzarServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
        $this->app->singleton(ZamzarService::class, function ($app) {
            return new ZamzarService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
