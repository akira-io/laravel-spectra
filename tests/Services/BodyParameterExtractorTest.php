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
