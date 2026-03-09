<?php

declare(strict_types=1);

namespace PhilipRehberger\Money\Laravel;

use Illuminate\Support\ServiceProvider;

/**
 * Optional Laravel service provider for php-money.
 *
 * Registers no critical bindings. Its main purpose is to allow publishing
 * a stub configuration file that documents available options, and to serve
 * as the auto-discovery entry point in composer.json.
 *
 * Auto-discovered via the "extra.laravel.providers" key in composer.json.
 */
class MoneyServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap package services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/money.php' => config_path('money.php'),
            ], 'money-config');
        }
    }

    /**
     * Register package services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/money.php', 'money');
    }
}
