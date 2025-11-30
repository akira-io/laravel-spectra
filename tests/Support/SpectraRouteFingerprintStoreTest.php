<?php

declare(strict_types=1);

use Akira\Spectra\Support\SpectraRouteFingerprintStore;
use Illuminate\Filesystem\Filesystem;

it('stores and retrieves fingerprint', function () {
    $filesystem = app(Filesystem::class);
    $store = new SpectraRouteFingerprintStore($filesystem);

    $fingerprint = 'test-fingerprint-'.bin2hex(random_bytes(16));
    $store->put($fingerprint);

    $retrieved = $store->get();
    expect($retrieved)->toBe($fingerprint);
});

it('detects when fingerprint has changed', function () {
    $filesystem = app(Filesystem::class);
    $store = new SpectraRouteFingerprintStore($filesystem);

    $fingerprint1 = 'fingerprint-1-'.bin2hex(random_bytes(16));
    $store->put($fingerprint1);

    $fingerprint2 = 'fingerprint-2-'.bin2hex(random_bytes(16));

    expect($store->changed($fingerprint2))->toBeTrue()
        ->and($store->changed($fingerprint1))->toBeFalse();
});

it('detects fingerprint as unchanged when same value stored', function () {
    $filesystem = app(Filesystem::class);
    $store = new SpectraRouteFingerprintStore($filesystem);

    $fingerprint = 'test-fingerprint-same-'.bin2hex(random_bytes(8));
    $store->put($fingerprint);

    expect($store->changed($fingerprint))->toBeFalse();
});

it('returns null when get is called on non-existent file', function () {
    $filesystem = app(Filesystem::class);
    $store = new SpectraRouteFingerprintStore($filesystem);

    $path = storage_path('framework/spectra/routes.hash');
    $filesystem->delete($path);

    $result = $store->get();
    expect($result)->toBeNull();
});

it('creates directory when putting fingerprint if it does not exist', function () {
    $filesystem = app(Filesystem::class);
    $store = new SpectraRouteFingerprintStore($filesystem);

    $path = storage_path('framework/spectra/routes.hash');
    $directory = dirname($path);
    $filesystem->deleteDirectory(dirname($directory));

    $fingerprint = 'test-new-dir-'.bin2hex(random_bytes(8));
    $store->put($fingerprint);

    expect($filesystem->exists($path))->toBeTrue();
});
