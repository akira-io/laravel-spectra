<?php

declare(strict_types=1);

it('returns cookie collection', function () {
    $response = $this->get('/spectra/cookies');

    expect($response->status())->toBe(200);
});

it('returns json response', function () {
    $response = $this->get('/spectra/cookies');

    expect($response->json())->toBeArray();
});

it('returns array of cookies', function () {
    $response = $this->get('/spectra/cookies');

    expect(is_array($response->json()))->toBeTrue();
});

it('returns 404 when spectra is disabled', function () {
    config()->set('spectra.enabled', false);

    $response = $this->get('/spectra/cookies');

    expect($response->status())->toBe(404);
});

it('requires spectra enabled middleware', function () {
    config()->set('spectra.enabled', true);
    config()->set('spectra.require_auth', false);

    $response = $this->get('/spectra/cookies');

    expect($response->status())->toBe(200);
});
