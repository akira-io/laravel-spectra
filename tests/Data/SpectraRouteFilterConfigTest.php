<?php

declare(strict_types=1);

use Akira\Spectra\Data\SpectraRouteFilterConfig;

it('creates config from array with routes', function () {
    $data = [
        'include_routes' => ['api/*', 'v1/*'],
        'exclude_routes' => ['spectra', 'admin'],
    ];

    $config = SpectraRouteFilterConfig::fromArray($data);

    expect($config->includeRoutes)->toBe(['api/*', 'v1/*'])
        ->and($config->excludeRoutes)->toBe(['spectra', 'admin']);
});

it('normalizes to indexed array when given associative array', function () {
    $data = [
        'include_routes' => ['api/*' => 'value', 'v1/*' => 'value2'],
        'exclude_routes' => ['spectra' => 'value'],
    ];

    $config = SpectraRouteFilterConfig::fromArray($data);

    expect($config->includeRoutes)->toBeArray()
        ->and(count($config->includeRoutes))->toBe(2);
});

it('converts to array', function () {
    $config = new SpectraRouteFilterConfig(
        includeRoutes: ['api/*'],
        excludeRoutes: ['spectra', 'admin'],
    );

    $array = $config->toArray();

    expect($array)->toHaveKeys(['include_routes', 'exclude_routes'])
        ->and($array['include_routes'])->toBe(['api/*'])
        ->and($array['exclude_routes'])->toBe(['spectra', 'admin']);
});
