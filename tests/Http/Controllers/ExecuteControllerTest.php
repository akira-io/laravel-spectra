<?php

declare(strict_types=1);

it('returns 405 on get request', function () {
    $response = $this->get('/spectra/execute');

    expect($response->status())->toBe(405);
});

it('accepts post request', function () {
    $response = $this->post('/spectra/execute', [
        'endpoint' => '/api/test',
        'method' => 'GET',
        'auth_mode' => 'current',
    ]);

    expect($response->status())->not->toBe(405);
});

it('returns json response on success', function () {
    $response = $this->post('/spectra/execute', [
        'endpoint' => '/api/test',
        'method' => 'GET',
        'auth_mode' => 'current',
    ]);

    expect($response->headers->get('content-type'))->toContain('json');
});

it('returns 404 when spectra is disabled', function () {
    config()->set('spectra.enabled', false);

    $response = $this->post('/spectra/execute', [
        'endpoint' => '/api/test',
        'method' => 'GET',
        'auth_mode' => 'current',
    ]);

    expect($response->status())->toBe(404);
});

it('respects require_auth configuration', function () {
    config()->set('spectra.require_auth', true);

    $response = $this->post('/spectra/execute', [
        'endpoint' => '/api/test',
        'method' => 'GET',
        'auth_mode' => 'current',
    ]);

    expect($response->status())->toBeIn([302, 401, 403]);
});

it('endpoint is accessible when auth disabled', function () {
    config()->set('spectra.require_auth', false);

    $response = $this->post('/spectra/execute', [
        'endpoint' => '/api/test',
        'method' => 'GET',
        'auth_mode' => 'current',
    ]);

    expect($response->status())->not->toBe(404);
});

it('endpoint returns valid response structure', function () {
    $response = $this->post('/spectra/execute', [
        'endpoint' => '/api/test',
        'method' => 'GET',
        'auth_mode' => 'current',
    ]);

    $json = $response->json();

    expect($json)->toHaveKey('status')
        ->and($json)->toHaveKey('time_ms')
        ->and($json)->toHaveKey('size_bytes')
        ->and($json)->toHaveKey('headers')
        ->and($json)->toHaveKey('body');
});

it('handles different http methods', function () {
    $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];

    foreach ($methods as $method) {
        $response = $this->post('/spectra/execute', [
            'endpoint' => '/api/test',
            'method' => $method,
            'auth_mode' => 'current',
        ]);

        expect($response->status())->not->toBe(405);
    }
});
