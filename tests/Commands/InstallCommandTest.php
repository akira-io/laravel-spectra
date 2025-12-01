<?php

declare(strict_types=1);

it('returns success exit code', function () {
    $this->artisan('spectra:install')
        ->assertExitCode(0);
});

it('publishes configuration', function () {
    $this->artisan('spectra:install');

    expect(function () {
        config('spectra');
    })->not->toThrow(Exception::class);
});

it('command executes successfully', function () {
    $this->artisan('spectra:install')
        ->assertExitCode(0);
});
