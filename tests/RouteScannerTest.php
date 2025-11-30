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
