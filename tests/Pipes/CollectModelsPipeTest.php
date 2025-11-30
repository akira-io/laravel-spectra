<?php

declare(strict_types=1);

use Akira\Spectra\Data\SpectraPayloadVO;
use Akira\Spectra\Pipes\CollectModelsPipe;

it('collects models from app/Models directory', function () {
    $pipe = app(CollectModelsPipe::class);

    $payload = new SpectraPayloadVO(
        routes: [],
        models: [],
        stats: [],
        version: '1.0.0',
        projectPath: '/app',
        fingerprint: '',
    );

    $result = $pipe(
        $payload,
        function (SpectraPayloadVO $p): SpectraPayloadVO {
            return $p;
        }
    );

    expect($result->models)->toBeArray();
});

it('preserves existing payload data', function () {
    $pipe = app(CollectModelsPipe::class);

    $payload = new SpectraPayloadVO(
        routes: ['test' => []],
        models: [],
        stats: ['existing' => 'stat'],
        version: '1.0.0',
        projectPath: '/app',
        fingerprint: 'abc123',
    );

    $result = $pipe(
        $payload,
        function (SpectraPayloadVO $p): SpectraPayloadVO {
            return $p;
        }
    );

    expect($result->routes)->toBe(['test' => []])
        ->and($result->stats)->toBe(['existing' => 'stat'])
        ->and($result->version)->toBe('1.0.0')
        ->and($result->fingerprint)->toBe('abc123');
});