<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;

it('executes sync desktop command successfully', function () {
    Http::fake([
        'http://localhost:3000/ingest' => Http::response(['status' => 'ok'], 200),
    ]);

    $this->artisan('spectra:sync-desktop')
        ->expectsOutput('Building Spectra payload...')
        ->expectsOutput('Sending payload to Spectra Desktop...')
        ->expectsOutput('Spectra Desktop sync completed successfully!')
        ->assertExitCode(0);
});

it('returns success code', function () {
    Http::fake([
        'http://localhost:3000/ingest' => Http::response(['status' => 'ok'], 200),
    ]);

    $this->artisan('spectra:sync-desktop')
        ->assertExitCode(0);
});
