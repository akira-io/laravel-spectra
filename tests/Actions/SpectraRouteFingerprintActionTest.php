<?php

declare(strict_types=1);

use Akira\Spectra\Actions\SpectraRouteFingerprintAction;

it('generates consistent fingerprints for same routes', function () {
    $action = new SpectraRouteFingerprintAction();

    $routes = [
        'route1' => ['uri' => '/api/test', 'methods' => ['GET']],
        'route2' => ['uri' => '/api/users', 'methods' => ['POST']],
    ];

    $fingerprint1 = $action->handle($routes);
    $fingerprint2 = $action->handle($routes);

    expect($fingerprint1)->toBe($fingerprint2);
});

it('generates different fingerprints for different routes', function () {
    $action = new SpectraRouteFingerprintAction();

    $routes1 = [
        'route1' => ['uri' => '/api/test', 'methods' => ['GET']],
    ];

    $routes2 = [
        'route1' => ['uri' => '/api/test', 'methods' => ['POST']],
    ];

    $fingerprint1 = $action->handle($routes1);
    $fingerprint2 = $action->handle($routes2);

    expect($fingerprint1)->not->toBe($fingerprint2);
});

it('generates sha256 fingerprint', function () {
    $action = new SpectraRouteFingerprintAction();

    $routes = ['route1' => ['uri' => '/api/test']];
    $fingerprint = $action->handle($routes);

    expect($fingerprint)->toHaveLength(64)
        ->and($fingerprint)->toMatch('/^[a-f0-9]{64}$/');
});

it('handles empty routes array', function () {
    $action = new SpectraRouteFingerprintAction();

    $routes = [];
    $fingerprint = $action->handle($routes);

    expect($fingerprint)->toBeString()
        ->and($fingerprint)->toHaveLength(64);
});

it('generates same fingerprint regardless of route order', function () {
    $action = new SpectraRouteFingerprintAction();

    $routes1 = [
        'route1' => ['uri' => '/api/test'],
        'route2' => ['uri' => '/api/users'],
        'route3' => ['uri' => '/api/posts'],
    ];

    $routes2 = [
        'route3' => ['uri' => '/api/posts'],
        'route1' => ['uri' => '/api/test'],
        'route2' => ['uri' => '/api/users'],
    ];

    $fingerprint1 = $action->handle($routes1);
    $fingerprint2 = $action->handle($routes2);

    expect($fingerprint1)->toBe($fingerprint2);
});
