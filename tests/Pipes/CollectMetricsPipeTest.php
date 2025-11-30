<?php

declare(strict_types=1);

use Akira\Spectra\Data\SpectraPayloadVO;
use Akira\Spectra\Pipes\CollectMetricsPipe;

it('collects metrics for payload', function () {
    $pipe = new CollectMetricsPipe();
    $payload = new SpectraPayloadVO(
        routes: ['route1' => [], 'route2' => []],
        models: ['model1' => []],
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

    expect($result->stats)->toHaveKeys(['total_routes', 'total_models', 'php_version', 'laravel_version', 'timestamp'])
        ->and($result->stats['total_routes'])->toBe(2)
        ->and($result->stats['total_models'])->toBe(1)
        ->and($result->stats['php_version'])->toBe(PHP_VERSION);
});

it('includes timestamp in metrics', function () {
    $pipe = new CollectMetricsPipe();
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

    expect($result->stats['timestamp'])->toBeString()
        ->and(str_contains($result->stats['timestamp'], 'T'))->toBeTrue();
});
