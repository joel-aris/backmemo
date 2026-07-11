<?php

declare(strict_types=1);

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
    'allowed_origins' => array_filter(array_map('trim', explode(',', env(
        'FRONTEND_URLS',
        env('FRONTEND_URL', 'http://localhost:5173') . ',http://127.0.0.1:5173,http://localhost:5174,http://127.0.0.1:5174,http://localhost:5175,http://127.0.0.1:5175'
    )))),
    'allowed_origins_patterns' => [
        '#^http://localhost:51[0-9]{2}$#',
        '#^http://127\.0\.0\.1:51[0-9]{2}$#',
    ],
    'allowed_headers' => ['Content-Type', 'Accept', 'Authorization', 'X-Requested-With'],
    'exposed_headers' => ['X-Request-Id'],
    'max_age' => 600,
    'supports_credentials' => true,
];
