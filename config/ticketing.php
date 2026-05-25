<?php

declare(strict_types=1);

return [
    'default' => env('TICKETING_PROVIDER', 'spektrix'),

    'catalogue' => [
        'past_days' => (int) env('TICKETING_CATALOGUE_PAST_DAYS', 0),
        'future_days' => (int) env('TICKETING_CATALOGUE_FUTURE_DAYS', 730),
    ],

    'providers' => [
        'spektrix' => [
            'base_url' => env('SPEKTRIX_API_BASE_URL'),
            'connect_timeout' => (int) env('SPEKTRIX_CONNECT_TIMEOUT', 5),
            'timeout' => (int) env('SPEKTRIX_TIMEOUT', 20),
        ],
    ],
];
