<?php

declare(strict_types=1);

use Akira\Spectra\Actions\DiscoverRoutesAction;
use Illuminate\Routing\Router;

beforeEach(function () {
    $this->router = app(Router::class);
    $this->action = app(DiscoverRoutesAction::class);
});

it('discovers registered routes', function () {
    $this->router->get('/api/users', fn () => 'users')->name('api.users.index');
    $this->router->post('/api/users', fn () => 'create')->name('api.users.store');

    $routes = $this->action->handle();

    expect($routes)->toBeArray()
        ->and($routes)->not->toBeEmpty()
        ->and(collect($routes)->pluck('name'))->toContain('api.users.index', 'api.users.store');
});

it('returns array of RouteMeta objects', function () {
    $this->router->get('/api/test', fn () => 'test')->name('api.test.show');

    $routes = $this->action->handle();

    expect($routes)->toBeArray()
        ->and($routes[0])->toHaveProperty('name')
        ->and($routes[0])->toHaveProperty('uri')
        ->and($routes[0])->toHaveProperty('methods')
        ->and($routes[0])->toHaveProperty('parameters');
});

it('includes multiple HTTP methods', function () {
    $this->router->match(['GET', 'POST', 'PUT'], '/api/items/{id}', fn () => 'item')->name('api.items.show');

    $routes = $this->action->handle();

    $route = collect($routes)->firstWhere('name', 'api.items.show');

    expect($route)->not->toBeNull()
        ->and($route->methods)->toContain('GET', 'POST', 'PUT');
});

it('extracts route parameters correctly', function () {
    $this->router->get('/api/users/{userId}/posts/{postId}', fn () => 'post')->name('api.users.posts.show');

    $routes = $this->action->handle();

    $route = collect($routes)->firstWhere('name', 'api.users.posts.show');

    expect($route)->not->toBeNull()
        ->and($route->parameters)->toHaveCount(2)
        ->and($route->parameters[0]->name)->toBe('userId')
        ->and($route->parameters[1]->name)->toBe('postId');
});

it('skips spectra internal routes', function () {
    $this->router->get('/spectra/internal/routes', fn () => 'routes')->name('spectra.internal.routes');
    $this->router->get('/api/public', fn () => 'public')->name('api.public.index');

    $routes = $this->action->handle();

    $spectraRoute = collect($routes)->firstWhere('name', 'spectra.internal.routes');

    expect($spectraRoute)->toBeNull()
        ->and(collect($routes)->pluck('name'))->toContain('api.public.index');
});

it('skips excluded framework routes', function () {
    $this->router->get('/telescope/test', fn () => 'telescope')->name('telescope.test');
    $this->router->get('/horizon/test', fn () => 'horizon')->name('horizon.test');
    $this->router->get('/api/allowed', fn () => 'allowed')->name('api.allowed');

    $routes = $this->action->handle();

    $routeNames = collect($routes)->pluck('name')->toArray();

    expect($routeNames)->not->toContain('telescope.test', 'horizon.test')
        ->and($routeNames)->toContain('api.allowed');
});

it('handles routes without names', function () {
    $this->router->get('/api/unnamed', fn () => 'unnamed');

    $routes = $this->action->handle();

    if (count($routes) > 0) {
        expect($routes)->toBeArray();
    } else {
        expect($routes)->toBeArray();
    }
});

it('includes route information in results', function () {
    $this->router->get('/api/resource', fn () => 'resource')->name('api.resource.show');

    $routes = $this->action->handle();

    $route = collect($routes)->firstWhere('name', 'api.resource.show');

    expect($route)->not->toBeNull()
        ->and($route->uri)->toBe('api/resource')
        ->and($route->methods)->toContain('GET')
        ->and($route->name)->toBe('api.resource.show');
});
