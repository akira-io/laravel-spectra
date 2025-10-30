<?php

declare(strict_types=1);

return [
    'enabled' => env('SPECTRA_ENABLED', app()->environment('local')),
    'only_local' => env('SPECTRA_ONLY_LOCAL', true),
    'guard' => env('SPECTRA_GUARD', 'web'),
    'impersonation_gate' => 'use-spectra',
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
