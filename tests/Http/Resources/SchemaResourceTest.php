<?php

declare(strict_types=1);

use Akira\Spectra\Dto\SchemaSpec;
use Akira\Spectra\Http\Resources\SchemaResource;

it('transforms schema to array', function () {
    $schema = new SchemaSpec(
        routeIdentifier: 'api.test',
        method: 'GET',
        pathSchema: [],
        querySchema: [],
        bodySchema: [],
        headersSchema: [],
    );

    $resource = new SchemaResource($schema);
    $result = $resource->resolve();

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('route')
        ->and($result)->toHaveKey('method')
        ->and($result)->toHaveKey('schemas');
});

it('includes route identifier', function () {
    $schema = new SchemaSpec(
        routeIdentifier: 'my.route',
        method: 'GET',
        pathSchema: [],
        querySchema: [],
        bodySchema: [],
        headersSchema: [],
    );

    $resource = new SchemaResource($schema);
    $result = $resource->resolve();

    expect($result['route'])->toBe('my.route');
});

it('includes method', function () {
    $schema = new SchemaSpec(
        routeIdentifier: 'api.test',
        method: 'POST',
        pathSchema: [],
        querySchema: [],
        bodySchema: [],
        headersSchema: [],
    );

    $resource = new SchemaResource($schema);
    $result = $resource->resolve();

    expect($result['method'])->toBe('POST');
});

it('includes path schema', function () {
    $pathSchema = ['type' => 'object', 'properties' => []];

    $schema = new SchemaSpec(
        routeIdentifier: 'api.test',
        method: 'GET',
        pathSchema: $pathSchema,
        querySchema: [],
        bodySchema: [],
        headersSchema: [],
    );

    $resource = new SchemaResource($schema);
    $result = $resource->resolve();

    expect($result['schemas']['path'])->toBe($pathSchema);
});

it('includes query schema', function () {
    $querySchema = ['type' => 'object', 'properties' => []];

    $schema = new SchemaSpec(
        routeIdentifier: 'api.test',
        method: 'GET',
        pathSchema: [],
        querySchema: $querySchema,
        bodySchema: [],
        headersSchema: [],
    );

    $resource = new SchemaResource($schema);
    $result = $resource->resolve();

    expect($result['schemas']['query'])->toBe($querySchema);
});

it('includes body schema', function () {
    $bodySchema = ['type' => 'object', 'properties' => []];

    $schema = new SchemaSpec(
        routeIdentifier: 'api.test',
        method: 'POST',
        pathSchema: [],
        querySchema: [],
        bodySchema: $bodySchema,
        headersSchema: [],
    );

    $resource = new SchemaResource($schema);
    $result = $resource->resolve();

    expect($result['schemas']['body'])->toBe($bodySchema);
});

it('includes headers schema', function () {
    $headersSchema = ['type' => 'object', 'properties' => []];

    $schema = new SchemaSpec(
        routeIdentifier: 'api.test',
        method: 'GET',
        pathSchema: [],
        querySchema: [],
        bodySchema: [],
        headersSchema: $headersSchema,
    );

    $resource = new SchemaResource($schema);
    $result = $resource->resolve();

    expect($result['schemas']['headers'])->toBe($headersSchema);
});

it('includes all schemas', function () {
    $schema = new SchemaSpec(
        routeIdentifier: 'api.test',
        method: 'POST',
        pathSchema: ['path' => true],
        querySchema: ['query' => true],
        bodySchema: ['body' => true],
        headersSchema: ['headers' => true],
    );

    $resource = new SchemaResource($schema);
    $result = $resource->resolve();

    expect($result['schemas'])->toHaveKey('path')
        ->and($result['schemas'])->toHaveKey('query')
        ->and($result['schemas'])->toHaveKey('body')
        ->and($result['schemas'])->toHaveKey('headers');
});

it('handles different HTTP methods', function () {
    $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];

    foreach ($methods as $method) {
        $schema = new SchemaSpec(
            routeIdentifier: 'api.test',
            method: $method,
            pathSchema: [],
            querySchema: [],
            bodySchema: [],
            headersSchema: [],
        );

        expect($schema->method)->toBe($method);
    }
});
