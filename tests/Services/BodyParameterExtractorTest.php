<?php

declare(strict_types=1);

use Akira\Spectra\Services\BodyParameterExtractor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;

beforeEach(function () {
    $this->extractor = app(BodyParameterExtractor::class);
    $this->router = app(Router::class);
});

it('returns empty array for undefined route', function () {
    $this->router->get('/extract-test', fn () => 'test');

    $route = new Route(['GET'], '/extract-test', [
        'uses' => fn () => 'test',
    ]);

    $result = $this->extractor->extract($route);

    expect($result)->toBeArray();
});

it('handles routes with closure actions', function () {
    $route = new Route(['GET'], '/closure-route', [
        'uses' => fn () => response()->json(['ok' => true]),
    ]);

    $result = $this->extractor->extract($route);

    expect($result)->toBeArray();
});

it('returns empty array for non-existent controller', function () {
    $route = new Route(['GET'], '/test', [
        'uses' => 'NonExistentController@method',
        'controller' => 'NonExistentController@method',
    ]);

    $result = $this->extractor->extract($route);

    expect($result)->toBeArray();
});

it('extracts nothing from simple controller methods', function () {
    $route = new Route(['GET'], '/test', [
        'uses' => 'NonExistentController@simpleMethod',
        'controller' => 'NonExistentController@simpleMethod',
    ]);

    $result = $this->extractor->extract($route);

    expect($result)->toBeArray();
});

it('returns consistent structure for empty parameters', function () {
    $route = new Route(['GET'], '/test', [
        'uses' => fn () => 'test',
    ]);

    $result = $this->extractor->extract($route);

    expect($result)->toBeArray();

    foreach ($result as $field => $config) {
        expect($config)->toBeArray()
            ->and($config)->toHaveKey('type')
            ->and($config)->toHaveKey('required')
            ->and($config)->toHaveKey('rules');
    }
});

it('handles route without action controller', function () {
    $route = new Route(['GET'], '/test', [
        'uses' => fn () => 'response',
    ]);

    $result = $this->extractor->extract($route);

    expect($result)->toBeArray();
});

it('processes route with empty uses key', function () {
    $route = new Route(['GET'], '/test', [
        'uses' => null,
    ]);

    $result = $this->extractor->extract($route);

    expect($result)->toBeArray();
});

it('handles routes with no action defined', function () {
    $route = new Route(['GET'], '/test', []);

    $result = $this->extractor->extract($route);

    expect($result)->toBeArray();
});

it('extracts from closure route correctly', function () {
    $route = new Route(['GET'], '/closure', [
        'uses' => fn () => 'test',
    ]);

    $result = $this->extractor->extract($route);

    expect($result)->toBeArray();
});

it('handles reflection exception gracefully', function () {
    $route = new Route(['GET'], '/test', [
        'uses' => fn () => 'test',
    ]);

    $result = $this->extractor->extract($route);

    expect($result)->toBeArray();
});

it('processes route with controller and method', function () {
    $route = new Route(['GET'], '/test', [
        'controller' => 'TestController@index',
    ]);

    $result = $this->extractor->extract($route);

    expect($result)->toBeArray();
});

it('handles invalid controller class name', function () {
    $route = new Route(['GET'], '/test', [
        'controller' => 'InvalidNamespace\\NonExistent@method',
    ]);

    $result = $this->extractor->extract($route);

    expect($result)->toBeArray();
});

it('processes multiple routes without issues', function () {
    $route1 = new Route(['GET'], '/test1', ['uses' => fn () => 'test']);
    $route2 = new Route(['POST'], '/test2', ['uses' => fn () => 'test']);
    $route3 = new Route(['PUT'], '/test3', ['uses' => fn () => 'test']);

    $result1 = $this->extractor->extract($route1);
    $result2 = $this->extractor->extract($route2);
    $result3 = $this->extractor->extract($route3);

    expect($result1)->toBeArray()
        ->and($result2)->toBeArray()
        ->and($result3)->toBeArray();
});
