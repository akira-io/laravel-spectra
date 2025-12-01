<?php

declare(strict_types=1);

it('returns 200 on system metrics', function () {
    $response = $this->get('/spectra/system-metrics');

    expect($response->status())->toBe(200);
});

it('system metrics returns json', function () {
    $response = $this->get('/spectra/system-metrics');

    expect($response->json())->toBeArray();
});

it('system metrics includes memory data', function () {
    $response = $this->get('/spectra/system-metrics');

    expect($response->json())->toHaveKey('memory');
});

it('memory data includes used value', function () {
    $response = $this->get('/spectra/system-metrics');

    expect($response->json('memory'))->toHaveKey('used');
});

it('memory data includes limit', function () {
    $response = $this->get('/spectra/system-metrics');

    expect($response->json('memory'))->toHaveKey('limit');
});

it('memory data includes percentage', function () {
    $response = $this->get('/spectra/system-metrics');

    expect($response->json('memory'))->toHaveKey('percentage');
});

it('system metrics includes debug flag', function () {
    $response = $this->get('/spectra/system-metrics');

    expect($response->json())->toHaveKey('debug');
});

it('system metrics includes cache driver', function () {
    $response = $this->get('/spectra/system-metrics');

    expect($response->json())->toHaveKey('cache_driver');
});

it('system metrics includes queue driver', function () {
    $response = $this->get('/spectra/system-metrics');

    expect($response->json())->toHaveKey('queue_driver');
});

it('system metrics includes db connection', function () {
    $response = $this->get('/spectra/system-metrics');

    expect($response->json())->toHaveKey('db_connection');
});

it('returns proper content type', function () {
    $response = $this->get('/spectra/system-metrics');

    expect($response->headers->get('content-type'))->toContain('json');
});
