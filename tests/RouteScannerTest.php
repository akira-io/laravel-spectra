<?php

declare(strict_types=1);

use Akira\Spectra\Services\RouteScanner;
use Illuminate\Routing\Router;

beforeEach(function () {
    $this->router = app(Router::class);
    $this->scanner = new RouteScanner($this->router);
});

it('scans routes correctly', function () {
    $this->router->get('/test/{id}', fn () => 'test')->name('test.show');

    $routes = $this->scanner->scan();

    expect($routes)->toBeArray()
        ->and($routes)->not->toBeEmpty();
});

it('extracts route parameters', function () {
    $this->router->get('/users/{user}/posts/{post}', fn () => 'test')->name('users.posts.show');

    $routes = $this->scanner->scan();

    $route = collect($routes)->firstWhere('name', 'users.posts.show');

    expect($route)->not->toBeNull()
        ->and($route->parameters)->toHaveCount(2)
        ->and($route->parameters[0]->name)->toBe('user')
        ->and($route->parameters[1]->name)->toBe('post');
});

it('skips spectra routes', function () {
    $this->router->get('/spectra/test', fn () => 'test')->name('spectra.test');

    $routes = $this->scanner->scan();

    $spectraRoute = collect($routes)->firstWhere('name', 'spectra.test');

    expect($spectraRoute)->toBeNull();
});
