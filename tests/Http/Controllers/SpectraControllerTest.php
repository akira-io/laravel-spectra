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

it('memory used is formatted string', function () {
    $response = $this->get('/spectra/system-metrics');

    $memory = $response->json('memory.used');
    expect(is_string($memory))->toBeTrue();
});

it('memory percentage is numeric', function () {
    $response = $this->get('/spectra/system-metrics');

    $percentage = $response->json('memory.percentage');
    expect(is_numeric($percentage))->toBeTrue();
});

it('memory percentage is between 0 and 100', function () {
    $response = $this->get('/spectra/system-metrics');

    $percentage = $response->json('memory.percentage');
    expect($percentage >= 0 && $percentage <= 100)->toBeTrue();
});

it('cache driver is string', function () {
    $response = $this->get('/spectra/system-metrics');

    expect(is_string($response->json('cache_driver')))->toBeTrue();
});

it('queue driver is string', function () {
    $response = $this->get('/spectra/system-metrics');

    expect(is_string($response->json('queue_driver')))->toBeTrue();
});

it('db connection is string', function () {
    $response = $this->get('/spectra/system-metrics');

    expect(is_string($response->json('db_connection')))->toBeTrue();
});

it('debug flag is boolean', function () {
    $response = $this->get('/spectra/system-metrics');

    expect(is_bool($response->json('debug')))->toBeTrue();
});

it('system metrics response structure is consistent', function () {
    $response = $this->get('/spectra/system-metrics');

    $data = $response->json();
    expect($data)->toHaveKeys(['memory', 'debug', 'cache_driver', 'queue_driver', 'db_connection']);
});

it('memory structure includes all keys', function () {
    $response = $this->get('/spectra/system-metrics');

    $memory = $response->json('memory');
    expect($memory)->toHaveKeys(['used', 'limit', 'percentage']);
});

it('returns json response with correct status', function () {
    $response = $this->get('/spectra/system-metrics');

    expect($response->status())->toBe(200)
        ->and($response->headers->get('content-type'))->toContain('json');
});
