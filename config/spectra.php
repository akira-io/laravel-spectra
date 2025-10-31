<?php

declare(strict_types=1);

return [
    'enabled' => env('SPECTRA_ENABLED', env('APP_ENV') === 'local'),
    'only_local' => env('SPECTRA_ONLY_LOCAL', true),
    'require_auth' => env('SPECTRA_REQUIRE_AUTH', true),
    'guard' => env('SPECTRA_GUARD', 'web'),
    'impersonation_gate' => 'use-spectra',
    
    // Route filtering
    'include_routes' => [
        'api/*',  // Include all API routes
    ],
    'exclude_routes' => [
        'spectra',
        '_ignition',
        'sanctum',
        'telescope',
        'horizon',
        'pulse',
    ],
    
    'rate_limit' => [
        'max' => 60,
        'per_minutes' => 1,
    ],
    'strip_headers' => [
        'authorization',
        'cookie',
        'x-api-key',
    ],
    'mask_fields' => [
        'password',
        'token',
        'authorization',
        'api_key',
        'secret',
    ],
];
