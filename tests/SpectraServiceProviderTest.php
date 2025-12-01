<?php

declare(strict_types=1);

use Akira\Spectra\Support\ConfigManager;
use Illuminate\Support\Facades\Gate;

it('defines use-spectra gate', function () {
    expect(Gate::has('use-spectra'))->toBeTrue();
});

it('use-spectra gate returns a response', function () {
    $user = new class {};

    $result = Gate::inspect('use-spectra', $user);

    expect($result)->not->toBeNull();
});

it('use-spectra gate handles users with hasRole method', function () {
    $user = new class
    {
        public function hasRole($role): bool
        {
            return true;
        }
    };

    $result = Gate::inspect('use-spectra', $user);

    expect($result)->not->toBeNull();
});

it('service provider loads routes when enabled', function () {
    $routes = app('router')->getRoutes();
    $spectraRoute = $routes->getByName('spectra.system-metrics');

    expect($spectraRoute)->not->toBeNull();
});

it('service provider makes config manager available', function () {
    $configManager = app(ConfigManager::class);

    expect($configManager)->not->toBeNull()
        ->and($configManager->general())->not->toBeNull();
});
