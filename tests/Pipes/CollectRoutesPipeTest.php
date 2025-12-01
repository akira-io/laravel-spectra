<?php

declare(strict_types=1);

use Akira\Spectra\Data\SpectraPayloadVO;
use Akira\Spectra\Pipes\CollectRoutesPipe;

it('collects routes from router', function () {
    $pipe = app(CollectRoutesPipe::class);

    $payload = new SpectraPayloadVO(
        routes: [],
        models: [],
        stats: [],
        version: '1.0.0',
        projectPath: '/app',
        fingerprint: 'test'
    );

    $next = function (SpectraPayloadVO $p) {
        return $p;
    };

    $result = $pipe->__invoke($payload, $next);

    expect($result->routes)->toBeArray();
});

it('passes through other payload properties', function () {
    $pipe = app(CollectRoutesPipe::class);

    $payload = new SpectraPayloadVO(
        routes: [],
        models: ['User' => []],
        stats: ['count' => 1],
        version: '1.0.0',
        projectPath: '/app',
        fingerprint: 'test123'
    );

    $next = function (SpectraPayloadVO $p) {
        return $p;
    };

    $result = $pipe->__invoke($payload, $next);

    expect($result->models)->toBe(['User' => []])
        ->and($result->stats)->toBe(['count' => 1])
        ->and($result->version)->toBe('1.0.0')
        ->and($result->projectPath)->toBe('/app')
        ->and($result->fingerprint)->toBe('test123');
});

it('pipes payload to next handler', function () {
    $pipe = app(CollectRoutesPipe::class);

    $payload = new SpectraPayloadVO(
        routes: [],
        models: [],
        stats: [],
        version: '1.0.0',
        projectPath: '/app',
        fingerprint: 'test'
    );

    $called = false;
    $next = function (SpectraPayloadVO $p) use (&$called) {
        $called = true;
        return $p;
    };

    $pipe->__invoke($payload, $next);

    expect($called)->toBeTrue();
});

it('collects routes as serialized arrays', function () {
    $pipe = app(CollectRoutesPipe::class);

    $payload = new SpectraPayloadVO(
        routes: [],
        models: [],
        stats: [],
        version: '1.0.0',
        projectPath: '/app',
        fingerprint: 'test'
    );

    $next = function (SpectraPayloadVO $p) {
        return $p;
    };

    $result = $pipe->__invoke($payload, $next);

    expect($result->routes)->toBeArray();

    if (count($result->routes) > 0) {
        $firstRoute = current($result->routes);
        expect(is_array($firstRoute))->toBeTrue();
    }
});
