<?php

return [
    /*
     |--------------------------------------------------------------------------
     | Laravel money
     |--------------------------------------------------------------------------
     */
    'locale' => config('app.locale', 'en_US'),
    'defaultCurrency' => config('app.currency', 'USD'),
    'defaultFormatter' => null,
    'isoCurrenciesPath' => __DIR__ . '/../vendor/moneyphp/money/resources/currency.php',
    'currencies' => [
        'iso' => ['USD'],
    ],
];
