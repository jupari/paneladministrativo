<?php

namespace App\Providers;

use App\Services\EmpleadoService;
use Illuminate\Support\ServiceProvider;

class EmpleadoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
        $this->app->bind(EmpleadoService::class, function ($app) {
            return new EmpleadoService();
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
