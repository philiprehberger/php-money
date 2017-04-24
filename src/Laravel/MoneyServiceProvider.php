<?php

declare(strict_types=1);

namespace PhilipRehberger\Money\Laravel;

use Illuminate\Support\ServiceProvider;

/**
 * Optional Laravel service provider for php-money.
 *
 * Auto-discovered via the "extra.laravel.providers" key in composer.json.
 */
class MoneyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        //
    }

    public function register(): void
    {
        //
    }
}
