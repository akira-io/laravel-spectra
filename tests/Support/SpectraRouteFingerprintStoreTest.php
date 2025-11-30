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
