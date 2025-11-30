<?php

declare(strict_types=1);

use Akira\Spectra\Actions\SpectraRouteFingerprintAction;
use Akira\Spectra\Data\SpectraPayloadVO;
use Akira\Spectra\Pipes\NormalizePayloadPipe;

it('normalizes payload with fingerprint', function () {
    $action = new SpectraRouteFingerprintAction();
    $pipe = new NormalizePayloadPipe($action);

    $routes = ['route1' => ['uri' => '/api/test']];
    $payload = new SpectraPayloadVO(
        routes: $routes,
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

    expect($result->fingerprint)->toBeString()
        ->and($result->fingerprint)->not->toBeEmpty()
        ->and($result->fingerprint)->toHaveLength(64);
});

it('generates consistent fingerprints', function () {
    $action = new SpectraRouteFingerprintAction();
    $pipe = new NormalizePayloadPipe($action);

    $routes = ['route1' => ['uri' => '/api/test']];
    $payload = new SpectraPayloadVO(
        routes: $routes,
        models: [],
        stats: [],
        version: '1.0.0',
        projectPath: '/app',
        fingerprint: '',
    );

    $result1 = $pipe(
        $payload,
        function (SpectraPayloadVO $p): SpectraPayloadVO {
            return $p;
        }
    );

    $result2 = $pipe(
        $payload,
        function (SpectraPayloadVO $p): SpectraPayloadVO {
            return $p;
        }
    );

    expect($result1->fingerprint)->toBe($result2->fingerprint);
});
