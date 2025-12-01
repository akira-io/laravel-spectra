<?php

declare(strict_types=1);

it('returns 404 when spectra is disabled', function () {
    config()->set('spectra.enabled', false);

    $response = $this->get('/spectra/cookies');

    expect($response->status())->toBe(404);
});

it('returns 404 when only_local is enabled and not in local environment', function () {
    config()->set('spectra.enabled', true);
    config()->set('spectra.only_local', true);

    $this->app['env'] = 'production';

    $response = $this->get('/spectra/cookies');

    expect($response->status())->toBe(404);
});

it('allows request when spectra is enabled', function () {
    config()->set('spectra.enabled', true);
    config()->set('spectra.only_local', false);
    config()->set('spectra.require_auth', false);

    $response = $this->get('/spectra/cookies');

    expect($response->status())->not->toBe(404);
});

it('allows request when only_local is enabled and in local environment', function () {
    config()->set('spectra.enabled', true);
    config()->set('spectra.only_local', true);

    $this->app['env'] = 'local';

    $response = $this->get('/spectra/cookies');

    expect($response->status())->not->toBe(404);
});

it('allows request when only_local is disabled', function () {
    config()->set('spectra.enabled', true);
    config()->set('spectra.only_local', false);
    config()->set('spectra.require_auth', false);

    $response = $this->get('/spectra/cookies');

    expect($response->status())->not->toBe(404);
});

it('blocks access to schema endpoint when disabled', function () {
    config()->set('spectra.enabled', false);

    $response = $this->get('/spectra/schema');

    expect($response->status())->toBe(404);
});

it('blocks access to execute endpoint when disabled', function () {
    config()->set('spectra.enabled', false);

    $response = $this->post('/spectra/execute', [
        'endpoint' => '/api/test',
        'method' => 'GET',
        'auth_mode' => 'current',
    ]);

    expect($response->status())->toBe(404);
});
