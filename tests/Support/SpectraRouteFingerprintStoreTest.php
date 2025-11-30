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

it('returns null when fingerprint file does not exist', function () {
    $filesystem = app(Filesystem::class);
    $store = new SpectraRouteFingerprintStore($filesystem);

    $path = storage_path('framework/spectra/routes.hash');
    if ($filesystem->exists($path)) {
        $filesystem->delete($path);
    }

    expect($store->get())->toBeNull();
});

it('creates directory if it does not exist when storing fingerprint', function () {
    $filesystem = app(Filesystem::class);
    $store = new SpectraRouteFingerprintStore($filesystem);

    $directory = storage_path('framework/spectra');
    if ($filesystem->exists($directory)) {
        $filesystem->deleteDirectory($directory);
    }

    $fingerprint = 'test-fingerprint-mkdir-'.bin2hex(random_bytes(8));
    $store->put($fingerprint);

    expect($filesystem->exists($directory))->toBeTrue()
        ->and($store->get())->toBe($fingerprint);
});

it('returns null when file exists but is empty', function () {
    $filesystem = app(Filesystem::class);
    $store = new SpectraRouteFingerprintStore($filesystem);

    $path = storage_path('framework/spectra/routes.hash');
    $directory = dirname($path);

    if (! $filesystem->exists($directory)) {
        $filesystem->makeDirectory($directory, 0755, true);
    }

    $filesystem->put($path, '');

    expect($store->get())->toBeNull();
});
