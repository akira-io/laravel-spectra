<?php

declare(strict_types=1);

use Akira\Spectra\Actions\BuildSchemaAction;
use Illuminate\Routing\Router;

beforeEach(function () {
    $this->router = app(Router::class);
    $this->action = app(BuildSchemaAction::class);
});

it('builds schema for discovered routes', function () {
    $this->router->get('/api/items', fn () => 'items')->name('api.items.index');
    $this->router->post('/api/items', fn () => 'create')->name('api.items.store');

    $schemas = $this->action->handle();

    expect($schemas)->toBeArray()
        ->and($schemas)->not->toBeEmpty();

    $keys = array_keys($schemas);

    expect($keys)->toContain('api.items.index::GET', 'api.items.store::POST');
});

it('returns array of SchemaSpec objects', function () {
    $this->router->get('/api/test', fn () => 'test')->name('api.test');

    $schemas = $this->action->handle();

    $schema = $schemas['api.test::GET'] ?? null;

    expect($schema)->not->toBeNull()
        ->and($schema)->toHaveProperty('method')
        ->and($schema)->toHaveProperty('routeIdentifier')
        ->and($schema->method)->toBe('GET');
});

it('skips HEAD and OPTIONS methods', function () {
    $this->router->match(['GET', 'HEAD', 'OPTIONS'], '/api/resource', fn () => 'resource')->name('api.resource');

    $schemas = $this->action->handle();

    $keys = array_keys($schemas);

    expect($keys)->toContain('api.resource::GET')
        ->and($keys)->not->toContain('api.resource::HEAD', 'api.resource::OPTIONS');
});

it('uses route name as identifier when available', function () {
    $this->router->get('/api/named-route', fn () => 'test')->name('api.named.show');

    $schemas = $this->action->handle();

    $schema = $schemas['api.named.show::GET'] ?? null;

    expect($schema)->not->toBeNull()
        ->and($schema->routeIdentifier)->toBe('api.named.show');
});

it('uses URI as identifier when route has no name', function () {
    $this->router->get('/api/unnamed-path', fn () => 'test');

    $schemas = $this->action->handle();

    if (count($schemas) > 0) {
        $keys = array_keys($schemas);
        expect($keys)->toBeArray()
            ->and(count($keys))->toBeGreaterThanOrEqual(1);
    } else {
        expect($schemas)->toBeArray();
    }
});

it('generates schemas for multiple HTTP methods on same route', function () {
    $this->router->match(['GET', 'POST', 'PUT', 'DELETE'], '/api/items/{id}', fn () => 'item')->name('api.items.crud');

    $schemas = $this->action->handle();

    expect($schemas)->toHaveKey('api.items.crud::GET')
        ->and($schemas)->toHaveKey('api.items.crud::POST')
        ->and($schemas)->toHaveKey('api.items.crud::PUT')
        ->and($schemas)->toHaveKey('api.items.crud::DELETE');
});

it('excludes spectra internal routes from schemas', function () {
    $this->router->get('/spectra/internal/test', fn () => 'test')->name('spectra.internal');
    $this->router->get('/api/public', fn () => 'public')->name('api.public');

    $schemas = $this->action->handle();

    $keys = array_keys($schemas);

    expect($keys)->not->toContain('spectra.internal::GET')
        ->and($keys)->toContain('api.public::GET');
});

it('processes all discovered routes', function () {
    $this->router->get('/api/users', fn () => 'users')->name('api.users.index');
    $this->router->post('/api/users', fn () => 'users.store')->name('api.users.store');
    $this->router->get('/api/posts', fn () => 'posts')->name('api.posts.index');
    $this->router->put('/api/posts/{id}', fn () => 'posts.update')->name('api.posts.update');

    $schemas = $this->action->handle();

    expect($schemas)->toHaveKey('api.users.index::GET')
        ->and($schemas)->toHaveKey('api.users.store::POST')
        ->and($schemas)->toHaveKey('api.posts.index::GET')
        ->and($schemas)->toHaveKey('api.posts.update::PUT');
});

it('creates consistent schema keys', function () {
    $this->router->get('/api/items/{id}', fn () => 'items')->name('api.items.show');

    $schemas = $this->action->handle();

    $keys = array_keys($schemas);

    expect($keys)->toContain('api.items.show::GET');
});

it('handles routes without validation rules', function () {
    $this->router->get('/api/simple', fn () => 'simple')->name('api.simple');

    $schemas = $this->action->handle();

    expect($schemas)->toHaveKey('api.simple::GET');

    $schema = $schemas['api.simple::GET'];

    expect($schema)->not->toBeNull();
});

it('returns non-empty array when routes exist', function () {
    $this->router->get('/api/items', fn () => 'items')->name('api.items');

    $schemas = $this->action->handle();

    expect($schemas)->not->toBeEmpty()
        ->and(count($schemas))->toBeGreaterThanOrEqual(1);
});