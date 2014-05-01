<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    |
    | The ISO 4217 currency code used as the default when no currency is
    | explicitly provided (e.g. Money::zero() with no argument).
    |
    */

    'default_currency' => env('MONEY_DEFAULT_CURRENCY', 'USD'),

    /*
    |--------------------------------------------------------------------------
    | Default Locale
    |--------------------------------------------------------------------------
    |
    | The BCP 47 locale string used when formatting money values without an
    | explicit locale argument. Requires the ext-intl PHP extension.
    |
    | Examples: 'en_US', 'de_DE', 'fr_FR', 'ja_JP'
    |
    */

    'default_locale' => env('MONEY_DEFAULT_LOCALE', 'en_US'),

];
