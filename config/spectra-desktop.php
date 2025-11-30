<?php

declare(strict_types=1);

return [
    'enabled' => env('SPECTRA_DESKTOP_ENABLED', false),
    'desktop_url' => env('SPECTRA_DESKTOP_URL', 'http://localhost:8000'),
    'public_key' => env('SPECTRA_DESKTOP_PUBLIC_KEY', ''),
    'max_drift' => env('SPECTRA_DESKTOP_MAX_DRIFT', 20),
];
