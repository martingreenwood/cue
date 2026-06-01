<?php

declare(strict_types=1);

return [
    'default' => env('TICKETING_PROVIDER', 'spektrix'),

    'event_path_prefix' => env('EVENT_PATH_PREFIX', '/events'),

    'display_timezone' => env('TICKETING_DISPLAY_TIMEZONE', 'Europe/London'),

    'catalogue' => [
        'past_days' => (int) env('TICKETING_CATALOGUE_PAST_DAYS', 0),
        'future_days' => (int) env('TICKETING_CATALOGUE_FUTURE_DAYS', 730),
        'sync_enabled' => (bool) env('TICKETING_CATALOGUE_SYNC_ENABLED', false),
        'sync_cron' => env('TICKETING_CATALOGUE_SYNC_CRON', '0 * * * *'),
        'active_run_stale_after_minutes' => (int) env('TICKETING_CATALOGUE_ACTIVE_RUN_STALE_AFTER_MINUTES', 15),
    ],

    'pricing' => [
        'currency' => env('TICKETING_CURRENCY', 'GBP'),
        'sync_enabled' => (bool) env('TICKETING_PRICE_SYNC_ENABLED', false),
        'sync_cron' => env('TICKETING_PRICE_SYNC_CRON', '*/15 * * * *'),
        'stale_after_minutes' => (int) env('TICKETING_PRICE_STALE_AFTER_MINUTES', 60),
        // Accepted values: 'free' or 'monetary'. Controls how performances with a
        // display_from_price_minor of 0 are labelled in public-facing templates.
        'zero_price_display' => env('TICKETING_ZERO_PRICE_DISPLAY', 'free'),
    ],

    'journeys' => [
        'sync_enabled' => (bool) env('TICKETING_JOURNEY_SYNC_ENABLED', false),
        'sync_cron' => env('TICKETING_JOURNEY_SYNC_CRON', '*/30 * * * *'),
        'retry_attempts' => (int) env('TICKETING_JOURNEY_SYNC_RETRY_ATTEMPTS', 3),
        'retry_backoff_seconds' => array_values(array_filter(array_map(
            static fn (string $value): int => max((int) trim($value), 1),
            explode(',', (string) env('TICKETING_JOURNEY_SYNC_RETRY_BACKOFF_SECONDS', '10,30')),
        ))),
        'stale_after_minutes' => (int) env('TICKETING_JOURNEY_STALE_AFTER_MINUTES', 180),
        'stale_alert_cooldown_minutes' => (int) env('TICKETING_JOURNEY_STALE_ALERT_COOLDOWN_MINUTES', 60),
    ],

    'providers' => [
        'spektrix' => [
            'base_url' => env('SPEKTRIX_API_BASE_URL'),
            'customer_facing_base_url' => env('SPEKTRIX_CUSTOMER_FACING_BASE_URL'),
            'iframe_base_url' => env('SPEKTRIX_IFRAME_BASE_URL'),
            'custom_domain_confirmed' => (bool) env('SPEKTRIX_CUSTOM_DOMAIN_CONFIRMED', false),
            'connect_timeout' => (int) env('SPEKTRIX_CONNECT_TIMEOUT', 5),
            'timeout' => (int) env('SPEKTRIX_TIMEOUT', 20),
        ],
    ],
];
