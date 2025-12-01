<?php

declare(strict_types=1);

it('returns 403 when desktop integration is disabled', function () {
    config()->set('spectra-desktop.enabled', false);

    $response = $this->get('/spectra/desktop/ping');

    expect($response->status())->toBe(403);
});

it('returns 403 when missing signature headers', function () {
    config()->set('spectra-desktop.enabled', true);

    $response = $this->get('/spectra/desktop/ping');

    expect($response->status())->toBe(403);
});

it('returns 403 when x-spectra-token header missing', function () {
    config()->set('spectra-desktop.enabled', true);

    $response = $this->get('/spectra/desktop/ping', [
        'X-Spectra-Timestamp' => time(),
        'X-Spectra-Nonce' => 'nonce',
        'X-Spectra-Signature' => 'sig',
    ]);

    expect($response->status())->toBe(403);
});

it('returns 403 when x-spectra-timestamp header missing', function () {
    config()->set('spectra-desktop.enabled', true);

    $response = $this->get('/spectra/desktop/ping', [
        'X-Spectra-Token' => 'token',
        'X-Spectra-Nonce' => 'nonce',
        'X-Spectra-Signature' => 'sig',
    ]);

    expect($response->status())->toBe(403);
});

it('returns 403 when x-spectra-nonce header missing', function () {
    config()->set('spectra-desktop.enabled', true);

    $response = $this->get('/spectra/desktop/ping', [
        'X-Spectra-Token' => 'token',
        'X-Spectra-Timestamp' => time(),
        'X-Spectra-Signature' => 'sig',
    ]);

    expect($response->status())->toBe(403);
});

it('returns 403 when x-spectra-signature header missing', function () {
    config()->set('spectra-desktop.enabled', true);

    $response = $this->get('/spectra/desktop/ping', [
        'X-Spectra-Token' => 'token',
        'X-Spectra-Timestamp' => time(),
        'X-Spectra-Nonce' => 'nonce',
    ]);

    expect($response->status())->toBe(403);
});

it('returns 403 when token is invalid', function () {
    config()->set('spectra-desktop.enabled', true);
    config()->set('spectra-desktop.public_key', 'valid-key');

    $response = $this->get('/spectra/desktop/ping', [
        'X-Spectra-Token' => 'invalid-key',
        'X-Spectra-Timestamp' => time(),
        'X-Spectra-Nonce' => 'nonce',
        'X-Spectra-Signature' => 'sig',
    ]);

    expect($response->status())->toBe(403);
});

it('blocks request to ping when desktop disabled', function () {
    config()->set('spectra-desktop.enabled', false);

    $response = $this->get('/spectra/desktop/ping', [
        'X-Spectra-Token' => 'key',
        'X-Spectra-Timestamp' => time(),
        'X-Spectra-Nonce' => 'nonce',
        'X-Spectra-Signature' => 'sig',
    ]);

    expect($response->status())->toBe(403);
});

it('blocks request to export when desktop disabled', function () {
    config()->set('spectra-desktop.enabled', false);

    $response = $this->get('/spectra/desktop/export', [
        'X-Spectra-Token' => 'key',
        'X-Spectra-Timestamp' => time(),
        'X-Spectra-Nonce' => 'nonce',
        'X-Spectra-Signature' => 'sig',
    ]);

    expect($response->status())->toBe(403);
});

it('blocks post request to force-sync when desktop disabled', function () {
    config()->set('spectra-desktop.enabled', false);

    $response = $this->post('/spectra/desktop/force-sync', [], [
        'X-Spectra-Token' => 'key',
        'X-Spectra-Timestamp' => time(),
        'X-Spectra-Nonce' => 'nonce',
        'X-Spectra-Signature' => 'sig',
    ]);

    expect($response->status())->toBe(403);
});
