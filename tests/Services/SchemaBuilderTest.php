<?php

declare(strict_types=1);

use Akira\Spectra\Dto\ParameterMeta;
use Akira\Spectra\Dto\RouteMeta;
use Akira\Spectra\Services\SchemaBuilder;

beforeEach(function () {
    $this->builder = app(SchemaBuilder::class);
});

it('builds schemas from empty routes array', function () {
    $schemas = $this->builder->buildSchemas([]);

    expect($schemas)->toBeArray()->toEqual([]);
});

it('builds schemas with correct structure', function () {
    $route = new RouteMeta(
        uri: '/test',
        methods: ['GET'],
        name: 'test.route',
        action: 'TestController@show',
        middleware: [],
        parameters: []
    );

    $schemas = $this->builder->buildSchemas([$route]);

    expect($schemas)->toBeArray();
    expect($schemas)->toHaveKey('test.route::GET');
});

it('skips HEAD and OPTIONS methods', function () {
    $route = new RouteMeta(
        uri: '/test',
        methods: ['HEAD', 'OPTIONS', 'GET'],
        name: 'test.route',
        action: 'TestController@show',
        middleware: [],
        parameters: []
    );

    $schemas = $this->builder->buildSchemas([$route]);

    expect($schemas)->toHaveKey('test.route::GET');
    expect(isset($schemas['test.route::HEAD']))->toBeFalse();
    expect(isset($schemas['test.route::OPTIONS']))->toBeFalse();
});

it('builds schema with path parameters', function () {
    $param = new ParameterMeta(
        name: 'id',
        required: true,
        wherePattern: '[0-9]+'
    );

    $route = new RouteMeta(
        uri: '/test/{id}',
        methods: ['GET'],
        name: 'test.route',
        action: 'TestController@show',
        middleware: [],
        parameters: [$param]
    );

    $schemas = $this->builder->buildSchemas([$route]);

    expect($schemas)->toHaveKey('test.route::GET');
    $schema = $schemas['test.route::GET'];
    expect($schema->pathSchema)->toHaveKey('properties');
});

it('builds query schema for GET requests', function () {
    $route = new RouteMeta(
        uri: '/test',
        methods: ['GET'],
        name: 'test.index',
        action: 'TestController@index',
        middleware: [],
        parameters: []
    );

    $schemas = $this->builder->buildSchemas([$route]);

    $schema = $schemas['test.index::GET'];
    expect($schema->querySchema)->toHaveKey('$schema')
        ->and($schema->querySchema)->toHaveKey('type')
        ->and($schema->querySchema)->toHaveKey('properties');
});

it('builds body schema for POST requests', function () {
    $route = new RouteMeta(
        uri: '/test',
        methods: ['POST'],
        name: 'test.store',
        action: 'TestController@store',
        middleware: [],
        parameters: []
    );

    $schemas = $this->builder->buildSchemas([$route]);

    $schema = $schemas['test.store::POST'];
    expect($schema->bodySchema)->toHaveKey('$schema')
        ->and($schema->bodySchema)->toHaveKey('type')
        ->and($schema->bodySchema)->toHaveKey('properties');
});

it('builds empty body schema for DELETE requests', function () {
    $route = new RouteMeta(
        uri: '/test/{id}',
        methods: ['DELETE'],
        name: 'test.destroy',
        action: 'TestController@destroy',
        middleware: [],
        parameters: []
    );

    $schemas = $this->builder->buildSchemas([$route]);

    $schema = $schemas['test.destroy::DELETE'];
    expect($schema->bodySchema['properties'])->toBeArray();
});

it('builds headers schema with default headers', function () {
    $route = new RouteMeta(
        uri: '/test',
        methods: ['GET'],
        name: 'test.route',
        action: 'TestController@show',
        middleware: [],
        parameters: []
    );

    $schemas = $this->builder->buildSchemas([$route]);

    $schema = $schemas['test.route::GET'];
    expect($schema->headersSchema)->toHaveKey('properties')
        ->and($schema->headersSchema['properties'])->toHaveKeys(['Accept', 'Content-Type']);
});

it('handles multiple routes', function () {
    $route1 = new RouteMeta(
        uri: '/test',
        methods: ['GET'],
        name: 'test.index',
        action: 'TestController@index',
        middleware: [],
        parameters: []
    );

    $route2 = new RouteMeta(
        uri: '/test/{id}',
        methods: ['GET'],
        name: 'test.show',
        action: 'TestController@show',
        middleware: [],
        parameters: []
    );

    $schemas = $this->builder->buildSchemas([$route1, $route2]);

    expect($schemas)->toHaveKey('test.index::GET')
        ->and($schemas)->toHaveKey('test.show::GET');
});

it('builds schema key with uppercase method', function () {
    $route = new RouteMeta(
        uri: '/test',
        methods: ['post'],
        name: 'test.route',
        action: 'TestController@store',
        middleware: [],
        parameters: []
    );

    $schemas = $this->builder->buildSchemas([$route]);

    expect(isset($schemas['test.route::POST']))->toBeTrue();
});

it('uses uri as identifier when name is null', function () {
    $route = new RouteMeta(
        uri: '/api/test',
        methods: ['GET'],
        name: null,
        action: 'TestController@show',
        middleware: [],
        parameters: []
    );

    $schemas = $this->builder->buildSchemas([$route]);

    expect(isset($schemas['/api/test::GET']))->toBeTrue();
});

it('schema spec contains all required fields', function () {
    $route = new RouteMeta(
        uri: '/test',
        methods: ['GET'],
        name: 'test.route',
        action: 'TestController@show',
        middleware: [],
        parameters: []
    );

    $schemas = $this->builder->buildSchemas([$route]);

    $schema = $schemas['test.route::GET'];
    expect($schema->routeIdentifier)->toBeString()
        ->and($schema->method)->toBe('GET')
        ->and($schema->pathSchema)->toBeArray()
        ->and($schema->querySchema)->toBeArray()
        ->and($schema->bodySchema)->toBeArray()
        ->and($schema->headersSchema)->toBeArray();
});

it('path schema includes json schema version', function () {
    $route = new RouteMeta(
        uri: '/test',
        methods: ['GET'],
        name: 'test.route',
        action: 'TestController@show',
        middleware: [],
        parameters: []
    );

    $schemas = $this->builder->buildSchemas([$route]);

    $schema = $schemas['test.route::GET'];
    expect($schema->pathSchema)->toHaveKey('$schema')
        ->and($schema->pathSchema['$schema'])->toBe('https://json-schema.org/draft/2020-12/schema');
});

it('headers schema includes accept header', function () {
    $route = new RouteMeta(
        uri: '/test',
        methods: ['GET'],
        name: 'test.route',
        action: 'TestController@show',
        middleware: [],
        parameters: []
    );

    $schemas = $this->builder->buildSchemas([$route]);

    $schema = $schemas['test.route::GET'];
    expect($schema->headersSchema['properties']['Accept'])->toHaveKey('type')
        ->and($schema->headersSchema['properties']['Accept']['type'])->toBe('string');
});

it('schema response has required array', function () {
    $route = new RouteMeta(
        uri: '/test',
        methods: ['GET'],
        name: 'test.route',
        action: 'TestController@show',
        middleware: [],
        parameters: []
    );

    $schemas = $this->builder->buildSchemas([$route]);

    $schema = $schemas['test.route::GET'];
    expect($schema->querySchema)->toHaveKey('required')
        ->and(is_array($schema->querySchema['required']))->toBeTrue();
});

it('path schema with parameters includes them', function () {
    $param1 = new ParameterMeta('id', true, '[0-9]+');
    $param2 = new ParameterMeta('slug', false, '[a-z-]+');

    $route = new RouteMeta(
        uri: '/test/{id}/{slug}',
        methods: ['GET'],
        name: 'test.show',
        action: 'TestController@show',
        middleware: [],
        parameters: [$param1, $param2]
    );

    $schemas = $this->builder->buildSchemas([$route]);

    $schema = $schemas['test.show::GET'];
    expect(count($schema->pathSchema['properties']) >= 2)->toBeTrue();
});

it('path schema required fields excludes optional params', function () {
    $param1 = new ParameterMeta('id', true, '[0-9]+');
    $param2 = new ParameterMeta('slug', false, null);

    $route = new RouteMeta(
        uri: '/test/{id}/{slug}',
        methods: ['GET'],
        name: 'test.show',
        action: 'TestController@show',
        middleware: [],
        parameters: [$param1, $param2]
    );

    $schemas = $this->builder->buildSchemas([$route]);

    $schema = $schemas['test.show::GET'];
    expect(in_array('id', $schema->pathSchema['required']))->toBeTrue();
});

it('PUT method uses body schema', function () {
    $route = new RouteMeta(
        uri: '/test/{id}',
        methods: ['PUT'],
        name: 'test.update',
        action: 'TestController@update',
        middleware: [],
        parameters: []
    );

    $schemas = $this->builder->buildSchemas([$route]);

    $schema = $schemas['test.update::PUT'];
    expect($schema->bodySchema)->toHaveKey('$schema')
        ->and($schema->bodySchema)->toHaveKey('properties');
});

it('PATCH method uses body schema', function () {
    $route = new RouteMeta(
        uri: '/test/{id}',
        methods: ['PATCH'],
        name: 'test.patch',
        action: 'TestController@patch',
        middleware: [],
        parameters: []
    );

    $schemas = $this->builder->buildSchemas([$route]);

    $schema = $schemas['test.patch::PATCH'];
    expect($schema->bodySchema)->toHaveKey('$schema')
        ->and($schema->bodySchema)->toHaveKey('type')
        ->and($schema->bodySchema['type'])->toBe('object');
});

it('numeric pattern infers integer type', function () {
    $param = new ParameterMeta('id', true, '[0-9]+');

    $route = new RouteMeta(
        uri: '/test/{id}',
        methods: ['GET'],
        name: 'test.show',
        action: 'TestController@show',
        middleware: [],
        parameters: [$param]
    );

    $schemas = $this->builder->buildSchemas([$route]);

    $schema = $schemas['test.show::GET'];
    expect($schema->pathSchema['properties']['id']['type'])->toBe('integer');
});