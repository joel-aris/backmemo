<?php

declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'VALIDIKA'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => 'UTC',
    'locale' => 'fr',
    'fallback_locale' => 'fr',
    'faker_locale' => 'fr_FR',
    'cipher' => 'AES-256-CBC',
    'key' => env('APP_KEY'),
    'validika_signing_key' => env('VALIDIKA_SIGNING_KEY'),
    'maintenance' => [
        'driver' => 'file',
    ],
];
