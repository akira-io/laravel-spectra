<?php

declare(strict_types=1);

use Akira\Spectra\Data\SpectraPayloadVO;

it('creates payload with all properties', function () {
    $payload = new SpectraPayloadVO(
        routes: ['route1' => ['uri' => '/api/test']],
        models: ['model1' => ['name' => 'User', 'table' => 'users']],
        stats: ['total_routes' => 1, 'total_models' => 1],
        version: '1.0.0',
        projectPath: '/app',
        fingerprint: 'abc123',
    );

    expect($payload->routes)->toHaveCount(1)
        ->and($payload->models)->toHaveCount(1)
        ->and($payload->stats['total_routes'])->toBe(1)
        ->and($payload->version)->toBe('1.0.0')
        ->and($payload->projectPath)->toBe('/app')
        ->and($payload->fingerprint)->toBe('abc123');
});

it('converts to array', function () {
    $payload = new SpectraPayloadVO(
        routes: ['route1' => ['uri' => '/api/test']],
        models: ['model1' => ['name' => 'User']],
        stats: ['total' => 1],
        version: '2.0.0',
        projectPath: '/project',
        fingerprint: 'xyz789',
    );

    $array = $payload->toArray();

    expect($array)->toHaveKeys(['routes', 'models', 'stats', 'version', 'project_path', 'fingerprint'])
        ->and($array['version'])->toBe('2.0.0')
        ->and($array['project_path'])->toBe('/project')
        ->and($array['fingerprint'])->toBe('xyz789');
});

it('is readonly', function () {
    $payload = new SpectraPayloadVO(
        routes: [],
        models: [],
        stats: [],
        version: '1.0.0',
        projectPath: '/app',
        fingerprint: 'test',
    );

    expect($payload)->toBeInstanceOf(SpectraPayloadVO::class);
});
