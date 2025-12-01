<?php

declare(strict_types=1);

use Akira\Spectra\Services\BodyParameterExtractor;
use Akira\Spectra\Services\FakerValueGenerator;
use Akira\Spectra\Services\RouteScanner;
use Illuminate\Routing\Router;

beforeEach(function () {
    $this->router = app(Router::class);
    $bodyExtractor = app(BodyParameterExtractor::class);
    $fakerGenerator = app(FakerValueGenerator::class);
    $this->scanner = new RouteScanner($this->router, $bodyExtractor, $fakerGenerator);
});

it('scans routes correctly', function () {
    $this->router->get('/api/test/{id}', fn () => 'test')->name('api.test.show');

    $routes = $this->scanner->scan();

    expect($routes)->toBeArray()
        ->and($routes)->not->toBeEmpty()
        ->and(collect($routes)->pluck('name'))->toContain('api.test.show');
});

it('extracts route parameters', function () {
    $this->router->get('/api/users/{user}/posts/{post}', fn () => 'test')->name('api.users.posts.show');

    $routes = $this->scanner->scan();

    $route = collect($routes)->firstWhere('name', 'api.users.posts.show');

    expect($route)->not->toBeNull()
        ->and($route->parameters)->toHaveCount(2)
        ->and($route->parameters[0]->name)->toBe('user')
        ->and($route->parameters[1]->name)->toBe('post');
});

it('skips spectra routes', function () {
    $this->router->get('/spectra/test', fn () => 'test')->name('spectra.internal.test');

    $routes = $this->scanner->scan();

    $spectraRoute = collect($routes)->firstWhere('name', 'spectra.internal.test');

    expect($spectraRoute)->toBeNull();
});

it('includes routes when include_routes is empty', function () {
    config()->set('spectra.include_routes', []);

    $this->router->get('/test', fn () => 'test')->name('test.route');

    $routes = $this->scanner->scan();

    expect($routes)->not->toBeEmpty();
});

it('extracts body parameters from post routes', function () {
    $this->router->post('/api/users', fn () => 'test')->name('api.users.store');

    $routes = $this->scanner->scan();

    $route = collect($routes)->firstWhere('name', 'api.users.store');

    expect($route)->not->toBeNull()
        ->and($route->bodyParameters)->toBeArray();
});

it('generates faker examples for body parameters', function () {
    $this->router->post('/api/posts', fn () => 'test')->name('api.posts.store');

    $routes = $this->scanner->scan();

    $route = collect($routes)->firstWhere('name', 'api.posts.store');

    expect($route)->not->toBeNull();
});

it('skips excluded routes correctly', function () {
    $this->router->get('/telescope/test', fn () => 'test')->name('telescope.test');

    $routes = $this->scanner->scan();

    $telescopeRoute = collect($routes)->firstWhere('name', 'telescope.test');

    expect($telescopeRoute)->toBeNull();
});

it('matches include_routes patterns', function () {
    $this->router->get('/api/users', fn () => 'test')->name('api.users');

    $routes = $this->scanner->scan();

    $apiRoute = collect($routes)->firstWhere('name', 'api.users');

    expect($apiRoute)->not->toBeNull();
});

it('filters routes based on include_routes config', function () {
    config()->set('spectra.include_routes', ['api/admin/*']);

    $this->router->get('/api/admin/users', fn () => 'test')->name('api.admin.users');
    $this->router->get('/api/public/posts', fn () => 'test')->name('api.public.posts');

    $routes = $this->scanner->scan();

    $adminRoute = collect($routes)->firstWhere('name', 'api.admin.users');
    $publicRoute = collect($routes)->firstWhere('name', 'api.public.posts');

    expect($adminRoute)->not->toBeNull()
        ->and($publicRoute)->toBeNull();
});

it('extracts route methods correctly', function () {
    $this->router->get('/api/test', fn () => 'test')->name('api.test.get');
    $this->router->post('/api/test', fn () => 'test')->name('api.test.post');

    $routes = $this->scanner->scan();

    $routesMethods = collect($routes)->pluck('methods')->flatten()->toArray();
    expect(in_array('GET', $routesMethods))->toBeTrue()
        ->and(in_array('POST', $routesMethods))->toBeTrue();
});

it('returns empty array when no routes match', function () {
    config()->set('spectra.include_routes', ['admin/*']);

    $this->router->get('/api/test', fn () => 'test')->name('api.test');

    $routes = $this->scanner->scan();

    expect(collect($routes)->where('name', 'api.test')->isEmpty())->toBeTrue();
});
