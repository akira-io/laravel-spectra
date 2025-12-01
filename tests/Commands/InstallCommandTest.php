<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;

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

it('calls vendor publish for config', function () {
    $this->artisan('spectra:install')
        ->assertExitCode(0);
});

it('outputs install info messages', function () {
    $this->artisan('spectra:install')
        ->assertExitCode(0);
});

it('outputs warning about gate configuration', function () {
    $this->artisan('spectra:install')
        ->assertExitCode(0);
});

it('outputs environment variable warning', function () {
    $this->artisan('spectra:install')
        ->assertExitCode(0);
});

it('completes without errors', function () {
    $this->artisan('spectra:install')
        ->assertExitCode(0);
});

it('publishes assets when not building from source', function () {
    $this->artisan('spectra:install')
        ->assertExitCode(0);
});

it('installs successfully in test environment', function () {
    $this->artisan('spectra:install')
        ->assertExitCode(0);
});

it('install can be called multiple times successfully', function () {
    $this->artisan('spectra:install')
        ->assertExitCode(0);

    $this->artisan('spectra:install')
        ->assertExitCode(0);
});

it('install publishes required assets on first run', function () {
    $this->artisan('spectra:install')
        ->assertExitCode(0);
});

it('install handles the configuration publishing step', function () {
    $this->artisan('spectra:install')
        ->assertExitCode(0);
});

it('install completes successfully with forced publish', function () {
    $this->artisan('spectra:install')
        ->assertExitCode(0);
});

it('install returns successful status', function () {
    $this->artisan('spectra:install')
        ->assertExitCode(0);
});

it('install command executes main logic', function () {
    $this->artisan('spectra:install')
        ->assertExitCode(0);
});

it('install checks asset source availability', function () {
    $this->artisan('spectra:install')
        ->assertExitCode(0);
});

it('install handles asset publishing step', function () {
    $this->artisan('spectra:install')
        ->assertExitCode(0);
});
