<?php

declare(strict_types=1);

use Akira\Spectra\Data\SpectraDesktopConfig;

it('creates config from array', function () {
    $data = [
        'enabled' => true,
        'desktop_url' => 'http://localhost:8000',
        'public_key' => 'test-key',
        'max_drift' => 30,
    ];

    $config = SpectraDesktopConfig::fromArray($data);

    expect($config->enabled)->toBeTrue()
        ->and($config->desktopUrl)->toBe('http://localhost:8000')
        ->and($config->publicKey)->toBe('test-key')
        ->and($config->maxDrift)->toBe(30);
});

it('uses default values for missing keys', function () {
    $data = [];

    $config = SpectraDesktopConfig::fromArray($data);

    expect($config->enabled)->toBeFalse()
        ->and($config->desktopUrl)->toBe('')
        ->and($config->publicKey)->toBe('')
        ->and($config->maxDrift)->toBe(20);
});

it('converts to array', function () {
    $config = new SpectraDesktopConfig(
        enabled: true,
        desktopUrl: 'http://localhost:8000',
        publicKey: 'test-key',
        maxDrift: 30,
    );

    $array = $config->toArray();

    expect($array)->toHaveKey('enabled')
        ->and($array)->toHaveKey('desktop_url')
        ->and($array)->toHaveKey('public_key')
        ->and($array)->toHaveKey('max_drift')
        ->and($array['enabled'])->toBeTrue()
        ->and($array['desktop_url'])->toBe('http://localhost:8000');
});

it('creates config from app config', function () {
    config()->set('spectra-desktop', [
        'enabled' => true,
        'desktop_url' => 'http://test-desktop',
        'public_key' => 'config-key',
        'max_drift' => 15,
    ]);

    $config = SpectraDesktopConfig::fromConfig();

    expect($config->enabled)->toBeTrue()
        ->and($config->desktopUrl)->toBe('http://test-desktop')
        ->and($config->publicKey)->toBe('config-key')
        ->and($config->maxDrift)->toBe(15);
});
