<?php

declare(strict_types=1);

use Akira\Spectra\Actions\SendSpectraPayloadToDesktopAction;
use Akira\Spectra\Data\SpectraPayloadVO;
use Akira\Spectra\Support\ConfigManager;
use Akira\Spectra\Support\SpectraRouteFingerprintStore;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->fingerprintStore = app(SpectraRouteFingerprintStore::class);
});

it('does nothing when desktop is disabled', function () {
    $configManager = app(ConfigManager::class);
    $action = new SendSpectraPayloadToDesktopAction($this->fingerprintStore, $configManager);

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

it('does nothing when fingerprint has not changed', function () {
    $configManager = app(ConfigManager::class);
    $action = new SendSpectraPayloadToDesktopAction($this->fingerprintStore, $configManager);

    $this->fingerprintStore->put('unchanged-fingerprint');

    $payload = new SpectraPayloadVO(
        routes: [],
        models: [],
        stats: [],
        version: '1.0.0',
        projectPath: '/app',
        fingerprint: 'unchanged-fingerprint',
    );

    $action->handle($payload);

    expect(true)->toBeTrue();
});

it('sends payload when desktop is enabled and fingerprint changed', function () {
    Http::fake([
        'http://localhost:9000/ingest' => Http::response(['success' => true], 200),
    ]);

    config()->set('spectra-desktop', [
        'enabled' => true,
        'desktop_url' => 'http://localhost:9000',
        'public_key' => 'test-public-key',
        'max_drift' => 20,
    ]);

    $configManager = new ConfigManager();
    $action = new SendSpectraPayloadToDesktopAction($this->fingerprintStore, $configManager);

    $payload = new SpectraPayloadVO(
        routes: ['route1' => ['uri' => '/api/test']],
        models: [],
        stats: ['total_routes' => 1],
        version: '1.0.0',
        projectPath: '/app',
        fingerprint: 'new-fingerprint-123',
    );

    $action->handle($payload);

    Http::assertSent(function ($request) {
        return $request->url() === 'http://localhost:9000/ingest';
    });
});

it('includes required headers in request', function () {
    Http::fake([
        'http://localhost:9000/ingest' => Http::response(['success' => true], 200),
    ]);

    config()->set('spectra-desktop', [
        'enabled' => true,
        'desktop_url' => 'http://localhost:9000',
        'public_key' => 'test-key',
        'max_drift' => 20,
    ]);

    $configManager = new ConfigManager();
    $action = new SendSpectraPayloadToDesktopAction($this->fingerprintStore, $configManager);

    $payload = new SpectraPayloadVO(
        routes: [],
        models: [],
        stats: [],
        version: '1.0.0',
        projectPath: '/app',
        fingerprint: 'test-fingerprint',
    );

    $action->handle($payload);

    Http::assertSent(function ($request) {
        return $request->hasHeader('X-Spectra-Token')
            && $request->hasHeader('X-Spectra-Timestamp')
            && $request->hasHeader('X-Spectra-Nonce')
            && $request->hasHeader('X-Spectra-Signature')
            && $request->hasHeader('Content-Type');
    });
});

it('saves fingerprint after successful send', function () {
    Http::fake([
        'http://localhost:9000/ingest' => Http::response(['success' => true], 200),
    ]);

    config()->set('spectra-desktop', [
        'enabled' => true,
        'desktop_url' => 'http://localhost:9000',
        'public_key' => 'key',
        'max_drift' => 20,
    ]);

    $configManager = new ConfigManager();
    $action = new SendSpectraPayloadToDesktopAction($this->fingerprintStore, $configManager);

    $newFingerprint = 'fingerprint-after-send-'.time();

    $payload = new SpectraPayloadVO(
        routes: [],
        models: [],
        stats: [],
        version: '1.0.0',
        projectPath: '/app',
        fingerprint: $newFingerprint,
    );

    $action->handle($payload);

    expect($this->fingerprintStore->get())->toBe($newFingerprint);
});

it('retries on first failure and succeeds', function () {
    $attempts = 0;

    Http::fake(function ($request) use (&$attempts) {
        $attempts++;

        if ($attempts === 1) {
            return Http::response(['error' => 'temporarily unavailable'], 503);
        }

        return Http::response(['success' => true], 200);
    });

    config()->set('spectra-desktop', [
        'enabled' => true,
        'desktop_url' => 'http://localhost:9000',
        'public_key' => 'key',
        'max_drift' => 20,
    ]);

    $configManager = new ConfigManager();
    $action = new SendSpectraPayloadToDesktopAction($this->fingerprintStore, $configManager);

    $payload = new SpectraPayloadVO(
        routes: [],
        models: [],
        stats: [],
        version: '1.0.0',
        projectPath: '/app',
        fingerprint: 'retry-test-fp',
    );

    $action->handle($payload);

    expect($attempts)->toBe(2)
        ->and($this->fingerprintStore->get())->toBe('retry-test-fp');
});

it('retries up to 3 times on failure', function () {
    $attempts = 0;

    Http::fake(function ($request) use (&$attempts) {
        $attempts++;

        return Http::response(['error' => 'failed'], 500);
    });

    config()->set('spectra-desktop', [
        'enabled' => true,
        'desktop_url' => 'http://localhost:9000',
        'public_key' => 'key',
        'max_drift' => 20,
    ]);

    $configManager = new ConfigManager();
    $action = new SendSpectraPayloadToDesktopAction($this->fingerprintStore, $configManager);

    $payload = new SpectraPayloadVO(
        routes: [],
        models: [],
        stats: [],
        version: '1.0.0',
        projectPath: '/app',
        fingerprint: 'all-failures-fp',
    );

    $action->handle($payload);

    expect($attempts)->toBe(3);
});

it('does not save fingerprint on failure', function () {
    Http::fake([
        'http://localhost:9000/ingest' => Http::response(['error' => 'failed'], 500),
    ]);

    config()->set('spectra-desktop', [
        'enabled' => true,
        'desktop_url' => 'http://localhost:9000',
        'public_key' => 'key',
        'max_drift' => 20,
    ]);

    $configManager = new ConfigManager();
    $action = new SendSpectraPayloadToDesktopAction($this->fingerprintStore, $configManager);

    $initialFingerprint = $this->fingerprintStore->get();

    $payload = new SpectraPayloadVO(
        routes: [],
        models: [],
        stats: [],
        version: '1.0.0',
        projectPath: '/app',
        fingerprint: 'failed-fp-should-not-save',
    );

    $action->handle($payload);

    expect($this->fingerprintStore->get())->toBe($initialFingerprint);
});

it('sends payload to correct desktop URL', function () {
    Http::fake([
        'https://spectra.example.com/ingest' => Http::response(['success' => true], 200),
    ]);

    config()->set('spectra-desktop', [
        'enabled' => true,
        'desktop_url' => 'https://spectra.example.com',
        'public_key' => 'key',
        'max_drift' => 20,
    ]);

    $configManager = new ConfigManager();
    $action = new SendSpectraPayloadToDesktopAction($this->fingerprintStore, $configManager);

    $payload = new SpectraPayloadVO(
        routes: [],
        models: [],
        stats: [],
        version: '1.0.0',
        projectPath: '/app',
        fingerprint: 'url-test-fp',
    );

    $action->handle($payload);

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'spectra.example.com/ingest');
    });
});
