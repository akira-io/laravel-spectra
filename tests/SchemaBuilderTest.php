<?php

declare(strict_types=1);

use Akira\Spectra\Dto\RouteMeta;
use Akira\Spectra\Services\SchemaBuilder;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Routing\Router;

beforeEach(function () {
    $this->builder = new SchemaBuilder(
        app(Router::class),
        app(ValidationFactory::class)
    );
});

it('builds json schema from routes', function () {
    $routes = [
        new RouteMeta(
            uri: '/test',
            methods: ['GET'],
            name: 'test.index',
            action: null,
            middleware: [],
            parameters: []
        ),
    ];

    $schemas = $this->builder->buildSchemas($routes);

    expect($schemas)->toBeArray()
        ->and($schemas)->toHaveKey('test.index::GET');
});

it('generates proper json schema 2020-12 structure', function () {
    $routes = [
        new RouteMeta(
            uri: '/test',
            methods: ['POST'],
            name: 'test.store',
            action: null,
            middleware: [],
            parameters: []
        ),
    ];

    $schemas = $this->builder->buildSchemas($routes);
    $schema = $schemas['test.store::POST'];

    expect($schema->bodySchema)->toHaveKey('$schema')
        ->and($schema->bodySchema['$schema'])->toBe('https://json-schema.org/draft/2020-12/schema');
});
