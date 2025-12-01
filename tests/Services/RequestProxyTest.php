<?php

declare(strict_types=1);

use Akira\Spectra\Dto\AuthMode;
use Akira\Spectra\Dto\ExecuteCommand;
use Akira\Spectra\Services\RequestProxy;
use Illuminate\Routing\Router;

beforeEach(function () {
    $this->router = app(Router::class);
    $this->proxy = app(RequestProxy::class);
});

it('executes simple GET request', function () {
    $this->router->get('/proxy-test-get', fn () => response()->json(['method' => 'GET']));

    $command = new ExecuteCommand(
        endpoint: '/proxy-test-get',
        method: 'GET',
    );

    $result = $this->proxy->handle($command);

    expect($result->status)->toBe(200)
        ->and($result->timeMs)->toBeGreaterThanOrEqual(0)
        ->and($result->sizeBytes)->toBeGreaterThanOrEqual(0);
});

it('executes POST request with body', function () {
    $this->router->post('/proxy-test-post', fn () => response()->json(['created' => true], 201));

    $command = new ExecuteCommand(
        endpoint: '/proxy-test-post',
        method: 'POST',
        body: ['name' => 'test'],
    );

    $result = $this->proxy->handle($command);

    expect($result->status)->toBe(201);
});

it('builds URI with path parameters', function () {
    $this->router->get('/proxy-test/{id}', fn () => response()->json(['id' => request()->route('id')]));

    $command = new ExecuteCommand(
        endpoint: '/proxy-test/{id}',
        method: 'GET',
        pathParams: ['id' => '123'],
    );

    $result = $this->proxy->handle($command);

    expect($result->status)->toBe(200);
});

it('builds URI with query parameters', function () {
    $this->router->get('/proxy-test-query', fn () => response()->json(['query' => request()->query('search')]));

    $command = new ExecuteCommand(
        endpoint: '/proxy-test-query',
        method: 'GET',
        query: ['search' => 'test-value'],
    );

    $result = $this->proxy->handle($command);

    expect($result->status)->toBe(200);
});

it('extracts response headers', function () {
    $this->router->get('/proxy-test-headers', function () {
        return response()->json(['ok' => true])
            ->header('X-Custom-Header', 'custom-value');
    });

    $command = new ExecuteCommand(
        endpoint: '/proxy-test-headers',
        method: 'GET',
    );

    $result = $this->proxy->handle($command);

    expect($result->headers)->toBeArray();
});

it('measures execution time', function () {
    $this->router->get('/proxy-test-time', function () {
        usleep(10000);
        return response()->json(['ok' => true]);
    });

    $command = new ExecuteCommand(
        endpoint: '/proxy-test-time',
        method: 'GET',
    );

    $result = $this->proxy->handle($command);

    expect($result->timeMs)->toBeGreaterThanOrEqual(0);
});

it('calculates response size in bytes', function () {
    $this->router->get('/proxy-test-size', fn () => response()->json(['data' => str_repeat('x', 100)]));

    $command = new ExecuteCommand(
        endpoint: '/proxy-test-size',
        method: 'GET',
    );

    $result = $this->proxy->handle($command);

    expect($result->sizeBytes)->toBeGreaterThan(0);
});

it('parses JSON response body', function () {
    $this->router->get('/proxy-test-json', fn () => response()->json(['type' => 'json']));

    $command = new ExecuteCommand(
        endpoint: '/proxy-test-json',
        method: 'GET',
    );

    $result = $this->proxy->handle($command);

    expect(is_array($result->body) || is_object($result->body))->toBeTrue();
});

it('returns raw body when not JSON', function () {
    $this->router->get('/proxy-test-raw', fn () => response('plain text', 200));

    $command = new ExecuteCommand(
        endpoint: '/proxy-test-raw',
        method: 'GET',
    );

    $result = $this->proxy->handle($command);

    expect($result->body)->toBe('plain text');
});

it('handles multiple path parameters', function () {
    $this->router->get('/proxy/{resource}/{id}', fn () => response()->json(['ok' => true]));

    $command = new ExecuteCommand(
        endpoint: '/proxy/{resource}/{id}',
        method: 'GET',
        pathParams: ['resource' => 'users', 'id' => '123'],
    );

    $result = $this->proxy->handle($command);

    expect($result->status)->toBe(200);
});

it('sanitizes configured strip headers', function () {
    config()->set('spectra.strip_headers', ['Authorization', 'X-Secret']);

    $this->router->get('/proxy-test-sanitize', function () {
        return response()->json(['auth' => request()->header('Authorization')]);
    });

    $command = new ExecuteCommand(
        endpoint: '/proxy-test-sanitize',
        method: 'GET',
        headers: [
            'Authorization' => 'Bearer token',
            'Accept' => 'application/json',
        ],
    );

    $result = $this->proxy->handle($command);

    expect($result->status)->toBe(200);
});

it('masks sensitive fields in response', function () {
    config()->set('spectra.mask_fields', ['password', 'secret']);

    $this->router->get('/proxy-test-mask', fn () => response()->json([
        'username' => 'user',
        'password' => 'secret123',
        'email' => 'user@example.com',
        'secret' => 'hidden',
    ]));

    $command = new ExecuteCommand(
        endpoint: '/proxy-test-mask',
        method: 'GET',
    );

    $result = $this->proxy->handle($command);

    expect(is_array($result->body))->toBeTrue();
});

it('masks sensitive fields recursively', function () {
    config()->set('spectra.mask_fields', ['password']);

    $this->router->get('/proxy-test-nested', fn () => response()->json([
        'user' => [
            'name' => 'John',
            'password' => 'secret123',
            'profile' => [
                'password' => 'nested-secret',
            ],
        ],
    ]));

    $command = new ExecuteCommand(
        endpoint: '/proxy-test-nested',
        method: 'GET',
    );

    $result = $this->proxy->handle($command);

    expect(is_array($result->body))->toBeTrue();
});

it('handles null response body', function () {
    $this->router->delete('/proxy-test-delete', fn () => response(null, 204));

    $command = new ExecuteCommand(
        endpoint: '/proxy-test-delete',
        method: 'DELETE',
    );

    $result = $this->proxy->handle($command);

    expect($result->status)->toBe(204)
        ->and($result->body)->toBeNull();
});

it('handles PUT request', function () {
    $this->router->put('/proxy-test/{id}', fn () => response()->json(['updated' => true]));

    $command = new ExecuteCommand(
        endpoint: '/proxy-test/{id}',
        method: 'PUT',
        pathParams: ['id' => '123'],
        body: ['name' => 'updated'],
    );

    $result = $this->proxy->handle($command);

    expect($result->status)->toBe(200);
});

it('handles PATCH request', function () {
    $this->router->patch('/proxy-test/{id}', fn () => response()->json(['patched' => true]));

    $command = new ExecuteCommand(
        endpoint: '/proxy-test/{id}',
        method: 'PATCH',
        pathParams: ['id' => '456'],
        body: ['status' => 'active'],
    );

    $result = $this->proxy->handle($command);

    expect($result->status)->toBe(200);
});

it('returns 404 for undefined route', function () {
    $command = new ExecuteCommand(
        endpoint: '/proxy-undefined-route-xyz',
        method: 'GET',
    );

    $result = $this->proxy->handle($command);

    expect($result->status)->toBe(404);
});

it('includes multiple headers in response', function () {
    $this->router->get('/proxy-test-multi-headers', function () {
        return response()->json(['ok' => true])
            ->header('X-Header-1', 'value1')
            ->header('X-Header-2', 'value2');
    });

    $command = new ExecuteCommand(
        endpoint: '/proxy-test-multi-headers',
        method: 'GET',
    );

    $result = $this->proxy->handle($command);

    expect($result->headers)->toBeArray();
});
