<?php

declare(strict_types=1);

use Akira\Spectra\Data\SpectraPayloadVO;
use Akira\Spectra\Http\Resources\SpectraPayloadResource;

it('transforms payload to array', function () {
    $payload = new SpectraPayloadVO(
        routes: [],
        models: [],
        stats: [],
        version: '1.0.0',
        projectPath: '/app',
        fingerprint: 'abc123',
    );

    $resource = new SpectraPayloadResource($payload);
    $result = $resource->resolve();

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('routes')
        ->and($result)->toHaveKey('models')
        ->and($result)->toHaveKey('stats')
        ->and($result)->toHaveKey('version')
        ->and($result)->toHaveKey('project_path')
        ->and($result)->toHaveKey('fingerprint');
});

it('includes routes', function () {
    $routeData = ['route1' => ['uri' => '/test']];

    $payload = new SpectraPayloadVO(
        routes: $routeData,
        models: [],
        stats: [],
        version: '1.0.0',
        projectPath: '/app',
        fingerprint: 'abc123',
    );

    $resource = new SpectraPayloadResource($payload);
    $result = $resource->resolve();

    expect($result['routes'])->toBe($routeData);
});

it('includes models', function () {
    $modelData = ['User' => [], 'Post' => []];

    $payload = new SpectraPayloadVO(
        routes: [],
        models: $modelData,
        stats: [],
        version: '1.0.0',
        projectPath: '/app',
        fingerprint: 'abc123',
    );

    $resource = new SpectraPayloadResource($payload);
    $result = $resource->resolve();

    expect($result['models'])->toBe($modelData);
});

it('includes stats', function () {
    $statsData = ['total_routes' => 10, 'total_models' => 5];

    $payload = new SpectraPayloadVO(
        routes: [],
        models: [],
        stats: $statsData,
        version: '1.0.0',
        projectPath: '/app',
        fingerprint: 'abc123',
    );

    $resource = new SpectraPayloadResource($payload);
    $result = $resource->resolve();

    expect($result['stats'])->toBe($statsData);
});

it('includes version', function () {
    $payload = new SpectraPayloadVO(
        routes: [],
        models: [],
        stats: [],
        version: '2.0.0',
        projectPath: '/app',
        fingerprint: 'abc123',
    );

    $resource = new SpectraPayloadResource($payload);
    $result = $resource->resolve();

    expect($result['version'])->toBe('2.0.0');
});

it('includes project path', function () {
    $payload = new SpectraPayloadVO(
        routes: [],
        models: [],
        stats: [],
        version: '1.0.0',
        projectPath: '/home/user/myapp',
        fingerprint: 'abc123',
    );

    $resource = new SpectraPayloadResource($payload);
    $result = $resource->resolve();

    expect($result['project_path'])->toBe('/home/user/myapp');
});

it('includes fingerprint', function () {
    $payload = new SpectraPayloadVO(
        routes: [],
        models: [],
        stats: [],
        version: '1.0.0',
        projectPath: '/app',
        fingerprint: 'fingerprint-xyz',
    );

    $resource = new SpectraPayloadResource($payload);
    $result = $resource->resolve();

    expect($result['fingerprint'])->toBe('fingerprint-xyz');
});

it('delegates to payload toArray method', function () {
    $payload = new SpectraPayloadVO(
        routes: ['route' => []],
        models: ['Model' => []],
        stats: ['count' => 1],
        version: '1.0.0',
        projectPath: '/app',
        fingerprint: 'abc123',
    );

    $resource = new SpectraPayloadResource($payload);
    $result = $resource->resolve();

    expect($result)->toBe($payload->toArray());
});

it('handles empty collections', function () {
    $payload = new SpectraPayloadVO(
        routes: [],
        models: [],
        stats: [],
        version: '1.0.0',
        projectPath: '/app',
        fingerprint: 'abc123',
    );

    $resource = new SpectraPayloadResource($payload);
    $result = $resource->resolve();

    expect($result['routes'])->toBeArray()
        ->and($result['models'])->toBeArray()
        ->and($result['stats'])->toBeArray();
});

it('handles complex nested data', function () {
    $payload = new SpectraPayloadVO(
        routes: [
            'route1' => ['uri' => '/test', 'methods' => ['GET']],
            'route2' => ['uri' => '/api', 'methods' => ['POST']],
        ],
        models: [
            'User' => ['fields' => ['id', 'name']],
            'Post' => ['fields' => ['id', 'title']],
        ],
        stats: [
            'total_routes' => 2,
            'total_models' => 2,
            'timestamp' => time(),
        ],
        version: '1.0.0',
        projectPath: '/app',
        fingerprint: 'abc123',
    );

    $resource = new SpectraPayloadResource($payload);
    $result = $resource->resolve();

    expect($result)->not->toBeEmpty()
        ->and($result)->toHaveKey('routes')
        ->and($result)->toHaveKey('models')
        ->and($result)->toHaveKey('stats');
});
