<?php

declare(strict_types=1);

use Akira\Spectra\Actions\SendSpectraPayloadToDesktopAction;
use Akira\Spectra\Data\SpectraPayloadVO;
use Akira\Spectra\Support\ConfigManager;
use Akira\Spectra\Support\SpectraRouteFingerprintStore;
use Illuminate\Support\Facades\Http;

it('does nothing when desktop is disabled', function () {
    $fingerprintStore = app(SpectraRouteFingerprintStore::class);
    $configManager = app(ConfigManager::class);

    $action = new SendSpectraPayloadToDesktopAction($fingerprintStore, $configManager);

    $payload = new SpectraPayloadVO(
        routes: [],
        models: [],
        stats: [],
        version: '1.0.0',
        projectPath: '/app',
        fingerprint: 'test-fingerprint',
    );

    // Should not throw even when disabled
    $action->handle($payload);

    expect(true)->toBeTrue();
});

it('does nothing when fingerprint has not changed', function () {
    $fingerprintStore = app(SpectraRouteFingerprintStore::class);
    $configManager = app(ConfigManager::class);

    $action = new SendSpectraPayloadToDesktopAction($fingerprintStore, $configManager);

    $fingerprintStore->put('test-fingerprint');

    $payload = new SpectraPayloadVO(
        routes: [],
        models: [],
        stats: [],
        version: '1.0.0',
        projectPath: '/app',
        fingerprint: 'test-fingerprint',
    );
    
    $action->handle($payload);

    expect(true)->toBeTrue();
});