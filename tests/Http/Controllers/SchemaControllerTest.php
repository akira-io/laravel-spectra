<?php

declare(strict_types=1);

it('returns schema json response', function () {
    $response = $this->get('/spectra/schema');

    expect($response->status())->toBe(200);
});

it('includes routes in response', function () {
    $response = $this->get('/spectra/schema');

    expect($response->json())->toHaveKey('routes');
});

it('includes schemas in response', function () {
    $response = $this->get('/spectra/schema');

    expect($response->json())->toHaveKey('schemas');
});

it('routes is array', function () {
    $response = $this->get('/spectra/schema');

    expect($response->json('routes'))->toBeArray();
});

it('schemas is array', function () {
    $response = $this->get('/spectra/schema');

    expect($response->json('schemas'))->toBeArray();
});

it('returns 404 when spectra is disabled', function () {
    config()->set('spectra.enabled', false);

    $response = $this->get('/spectra/schema');

    expect($response->status())->toBe(404);
});

it('returns proper content type', function () {
    $response = $this->get('/spectra/schema');

    expect($response->headers->get('content-type'))->toContain('json');
});

it('returns valid json', function () {
    $response = $this->get('/spectra/schema');

    expect(json_decode($response->getContent()))->toBeObject();
});
