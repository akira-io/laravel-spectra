<?php

declare(strict_types=1);

use Akira\Spectra\Dto\ExecuteResult;
use Akira\Spectra\Http\Resources\ExecuteResultResource;
use Akira\Spectra\Dto\RouteMeta;
use Akira\Spectra\Http\Resources\RouteResource;

it('transforms execute result correctly', function () {
    $result = new ExecuteResult(
        status: 200,
        timeMs: 150,
        sizeBytes: 1024,
        headers: ['Content-Type' => 'application/json'],
        body: ['message' => 'success']
    );

    $resource = new ExecuteResultResource($result);
    $array = $resource->toArray(request());

    expect($array)->toHaveKey('status')
        ->and($array['status'])->toBe(200)
        ->and($array)->toHaveKey('time_ms')
        ->and($array['time_ms'])->toBe(150)
        ->and($array)->toHaveKey('size_bytes')
        ->and($array['size_bytes'])->toBe(1024);
});

it('transforms route meta correctly', function () {
    $route = new RouteMeta(
        uri: '/test/{id}',
        methods: ['GET', 'POST'],
        name: 'test.show',
        action: 'TestController@show',
        middleware: ['web', 'auth'],
        parameters: []
    );

    $resource = new RouteResource($route);
    $array = $resource->toArray(request());

    expect($array)->toHaveKey('uri')
        ->and($array['uri'])->toBe('/test/{id}')
        ->and($array)->toHaveKey('methods')
        ->and($array['methods'])->toBe(['GET', 'POST'])
        ->and($array)->toHaveKey('name')
        ->and($array['name'])->toBe('test.show');
});
