<?php

declare(strict_types=1);

it('returns 403 when desktop integration is disabled', function () {
    config()->set('spectra-desktop.enabled', false);

    $response = $this->get('/spectra/desktop/ping');

    expect($response->status())->toBe(403);
});

it('returns 403 when missing token header', function () {
    config()->set('spectra-desktop.enabled', true);

    $response = $this->get('/spectra/desktop/ping', [
        'X-Spectra-Timestamp' => time(),
        'X-Spectra-Nonce' => 'nonce',
        'X-Spectra-Signature' => 'sig',
    ]);

    expect($response->status())->toBe(403);
});

it('returns 403 when missing timestamp header', function () {
    config()->set('spectra-desktop.enabled', true);

    $response = $this->get('/spectra/desktop/ping', [
        'X-Spectra-Token' => 'token',
        'X-Spectra-Nonce' => 'nonce',
        'X-Spectra-Signature' => 'sig',
    ]);

    expect($response->status())->toBe(403);
});

it('returns 403 when missing nonce header', function () {
    config()->set('spectra-desktop.enabled', true);

    $response = $this->get('/spectra/desktop/ping', [
        'X-Spectra-Token' => 'token',
        'X-Spectra-Timestamp' => time(),
        'X-Spectra-Signature' => 'sig',
    ]);

    expect($response->status())->toBe(403);
});

it('returns 403 when missing signature header', function () {
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

it('blocks requests to ping when disabled', function () {
    config()->set('spectra-desktop.enabled', false);

    $response = $this->get('/spectra/desktop/ping', [
        'X-Spectra-Token' => 'key',
        'X-Spectra-Timestamp' => time(),
        'X-Spectra-Nonce' => 'nonce',
        'X-Spectra-Signature' => 'sig',
    ]);

    expect($response->status())->toBe(403);
});

it('blocks requests to export when disabled', function () {
    config()->set('spectra-desktop.enabled', false);

    $response = $this->get('/spectra/desktop/export', [
        'X-Spectra-Token' => 'key',
        'X-Spectra-Timestamp' => time(),
        'X-Spectra-Nonce' => 'nonce',
        'X-Spectra-Signature' => 'sig',
    ]);

    expect($response->status())->toBe(403);
});

it('blocks post requests to force-sync when disabled', function () {
    config()->set('spectra-desktop.enabled', false);

    $response = $this->post('/spectra/desktop/force-sync', [], [
        'X-Spectra-Token' => 'key',
        'X-Spectra-Timestamp' => time(),
        'X-Spectra-Nonce' => 'nonce',
        'X-Spectra-Signature' => 'sig',
    ]);

    expect($response->status())->toBe(403);
});

it('returns 403 when invalid signature provided', function () {
    config()->set('spectra-desktop.enabled', true);
    config()->set('spectra-desktop.public_key', 'test-key');

    $response = $this->get('/spectra/desktop/ping', [
        'X-Spectra-Token' => 'test-key',
        'X-Spectra-Timestamp' => (string) time(),
        'X-Spectra-Nonce' => 'test-nonce',
        'X-Spectra-Signature' => 'invalid-signature',
    ]);

    expect($response->status())->toBe(403);
});

it('returns 403 when timestamp exceeds max drift', function () {
    config()->set('spectra-desktop.enabled', true);
    config()->set('spectra-desktop.public_key', 'test-key');
    config()->set('spectra-desktop.max_drift', 5);

    $oldTimestamp = time() - 600;

    $response = $this->get('/spectra/desktop/ping', [
        'X-Spectra-Token' => 'test-key',
        'X-Spectra-Timestamp' => (string) $oldTimestamp,
        'X-Spectra-Nonce' => 'test-nonce',
        'X-Spectra-Signature' => 'sig',
    ]);

    expect($response->status())->toBe(403);
});

it('returns 403 when nonce is reused', function () {
    config()->set('spectra-desktop.enabled', true);
    config()->set('spectra-desktop.public_key', 'test-key');

    $nonce = 'reused-nonce-' . uniqid();
    $timestamp = (string) time();
    $publicKey = 'test-key';
    $bodyJson = '';
    $secret = $publicKey . $timestamp . $nonce;
    $signature = hash_hmac('sha256', $bodyJson, $secret, false);

    $this->get('/spectra/desktop/ping', [
        'X-Spectra-Token' => $publicKey,
        'X-Spectra-Timestamp' => $timestamp,
        'X-Spectra-Nonce' => $nonce,
        'X-Spectra-Signature' => $signature,
    ]);

    $response = $this->get('/spectra/desktop/ping', [
        'X-Spectra-Token' => $publicKey,
        'X-Spectra-Timestamp' => $timestamp,
        'X-Spectra-Nonce' => $nonce,
        'X-Spectra-Signature' => $signature,
    ]);

    expect($response->status())->toBe(403);
});

it('returns 403 when timestamp is invalid format', function () {
    config()->set('spectra-desktop.enabled', true);

    $response = $this->get('/spectra/desktop/ping', [
        'X-Spectra-Token' => 'key',
        'X-Spectra-Timestamp' => 'not-a-number',
        'X-Spectra-Nonce' => 'nonce',
        'X-Spectra-Signature' => 'sig',
    ]);

    expect($response->status())->toBe(403);
});
