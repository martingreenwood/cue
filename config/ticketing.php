<?php

declare(strict_types=1);

return [
    'default' => env('TICKETING_PROVIDER', 'spektrix'),

    'event_path_prefix' => env('EVENT_PATH_PREFIX', '/events'),

    'catalogue' => [
        'past_days' => (int) env('TICKETING_CATALOGUE_PAST_DAYS', 0),
        'future_days' => (int) env('TICKETING_CATALOGUE_FUTURE_DAYS', 730),
    ],

    'pricing' => [
        'currency' => env('TICKETING_CURRENCY', 'GBP'),
        'sync_enabled' => (bool) env('TICKETING_PRICE_SYNC_ENABLED', false),
        'sync_cron' => env('TICKETING_PRICE_SYNC_CRON', '*/15 * * * *'),
        'stale_after_minutes' => (int) env('TICKETING_PRICE_STALE_AFTER_MINUTES', 60),
    ],

    'providers' => [
        'spektrix' => [
            'base_url' => env('SPEKTRIX_API_BASE_URL'),
            'connect_timeout' => (int) env('SPEKTRIX_CONNECT_TIMEOUT', 5),
            'timeout' => (int) env('SPEKTRIX_TIMEOUT', 20),
        ],
    ],
];
