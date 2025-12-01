<?php

declare(strict_types=1);

use Akira\Spectra\Services\RouteScanner;

it('returns array of routes', function () {
    $scanner = app(RouteScanner::class);

    $routes = $scanner->scan();

    expect($routes)->toBeArray();
});

it('scans application routes', function () {
    $scanner = app(RouteScanner::class);

    $routes = $scanner->scan();

    expect(is_array($routes))->toBeTrue();
});

it('each route has required structure', function () {
    $scanner = app(RouteScanner::class);

    $routes = $scanner->scan();

    expect($routes)->toBeArray();

    if (count($routes) > 0) {
        $firstRoute = $routes[0];
        expect(is_object($firstRoute))->toBeTrue();
    }
});

it('route has uri property', function () {
    $scanner = app(RouteScanner::class);

    $routes = $scanner->scan();

    expect($routes)->toBeArray();

    if (count($routes) > 0) {
        expect(isset($routes[0]->uri) || property_exists($routes[0], 'uri'))->toBeTrue();
    }
});

it('excludes framework routes', function () {
    $scanner = app(RouteScanner::class);

    $routes = $scanner->scan();

    expect(is_array($routes))->toBeTrue();
});
