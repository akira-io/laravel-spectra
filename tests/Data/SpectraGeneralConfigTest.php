<?php

declare(strict_types=1);

use Akira\Spectra\Data\SpectraGeneralConfig;

it('creates config with all properties', function () {
    $config = new SpectraGeneralConfig(
        enabled: true,
        onlyLocal: false,
        requireAuth: true,
        guard: 'sanctum',
        impersonationGate: 'custom-gate',
    );

    expect($config->enabled)->toBeTrue()
        ->and($config->onlyLocal)->toBeFalse()
        ->and($config->requireAuth)->toBeTrue()
        ->and($config->guard)->toBe('sanctum')
        ->and($config->impersonationGate)->toBe('custom-gate');
});

it('converts to array with correct keys', function () {
    $config = new SpectraGeneralConfig(
        enabled: false,
        onlyLocal: true,
        requireAuth: false,
        guard: 'web',
        impersonationGate: 'use-spectra',
    );

    $array = $config->toArray();

    expect($array)->toHaveKeys(['enabled', 'only_local', 'require_auth', 'guard', 'impersonation_gate'])
        ->and($array['enabled'])->toBeFalse()
        ->and($array['only_local'])->toBeTrue()
        ->and($array['guard'])->toBe('web');
});
