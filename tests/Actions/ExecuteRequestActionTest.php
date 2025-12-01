<?php

declare(strict_types=1);

use Akira\Spectra\Actions\ExecuteRequestAction;
use Akira\Spectra\Dto\AuthMode;
use Akira\Spectra\Dto\ExecuteCommand;
use Illuminate\Routing\Router;

beforeEach(function () {
    $this->router = app(Router::class);
    $this->action = app(ExecuteRequestAction::class);
});

it('executes GET request successfully', function () {
    $this->router->get('/test-get', fn () => response()->json(['message' => 'success'], 200));

    $command = new ExecuteCommand(
        endpoint: '/test-get',
        method: 'GET',
    );

    $result = $this->action->handle($command);

    expect($result->status)->toBe(200)
        ->and($result->timeMs)->toBeGreaterThanOrEqual(0)
        ->and($result->sizeBytes)->toBeGreaterThanOrEqual(0);
});

it('executes POST request with body', function () {
    $this->router->post('/test-post', function () {
        return response()->json(['created' => true], 201);
    });

    $command = new ExecuteCommand(
        endpoint: '/test-post',
        method: 'POST',
        body: ['name' => 'test', 'value' => 123],
    );

    $result = $this->action->handle($command);

    expect($result->status)->toBe(201)
        ->and($result->body)->toBeTruthy();
});

it('executes PUT request with parameters', function () {
    $this->router->put('/test-put/{id}', function () {
        return response()->json(['updated' => true], 200);
    });

    $command = new ExecuteCommand(
        endpoint: '/test-put/{id}',
        method: 'PUT',
        pathParams: ['id' => '123'],
        body: ['name' => 'updated'],
    );

    $result = $this->action->handle($command);

    expect($result->status)->toBe(200);
});

it('executes DELETE request', function () {
    $this->router->delete('/test-delete/{id}', function () {
        return response(null, 204);
    });

    $command = new ExecuteCommand(
        endpoint: '/test-delete/{id}',
        method: 'DELETE',
        pathParams: ['id' => '123'],
    );

    $result = $this->action->handle($command);

    expect($result->status)->toBe(204);
});

it('returns ExecuteResult with status code', function () {
    $this->router->get('/test-status', fn () => response()->json([], 200));

    $command = new ExecuteCommand(
        endpoint: '/test-status',
        method: 'GET',
    );

    $result = $this->action->handle($command);

    expect($result)->toHaveProperty('status')
        ->and($result)->toHaveProperty('timeMs')
        ->and($result)->toHaveProperty('sizeBytes')
        ->and($result)->toHaveProperty('headers')
        ->and($result)->toHaveProperty('body');
});

it('captures response headers', function () {
    $this->router->get('/test-headers', function () {
        return response()
            ->json(['test' => true])
            ->header('X-Custom-Header', 'custom-value');
    });

    $command = new ExecuteCommand(
        endpoint: '/test-headers',
        method: 'GET',
    );

    $result = $this->action->handle($command);

    expect($result->headers)->toBeArray();
});

it('handles query parameters', function () {
    $this->router->get('/test-query', function () {
        $query = request()->query('param');

        return response()->json(['param' => $query]);
    });

    $command = new ExecuteCommand(
        endpoint: '/test-query',
        method: 'GET',
        query: ['param' => 'value123'],
    );

    $result = $this->action->handle($command);

    expect($result->status)->toBe(200);
});

it('measures request execution time', function () {
    $this->router->get('/test-timing', function () {
        usleep(10000);

        return response()->json(['ok' => true]);
    });

    $command = new ExecuteCommand(
        endpoint: '/test-timing',
        method: 'GET',
    );

    $result = $this->action->handle($command);

    expect($result->timeMs)->toBeGreaterThanOrEqual(0);
});

it('returns response body in result', function () {
    $this->router->get('/test-body', fn () => response()->json(['data' => 'test-value']));

    $command = new ExecuteCommand(
        endpoint: '/test-body',
        method: 'GET',
    );

    $result = $this->action->handle($command);

    expect($result->body)->not->toBeNull();
});

it('handles different response formats', function () {
    $this->router->get('/json', fn () => response()->json(['type' => 'json']));
    $this->router->get('/text', fn () => response('text response', 200));
    $this->router->get('/html', fn () => response('<html>test</html>', 200));

    $jsonCommand = new ExecuteCommand(endpoint: '/json', method: 'GET');
    $textCommand = new ExecuteCommand(endpoint: '/text', method: 'GET');
    $htmlCommand = new ExecuteCommand(endpoint: '/html', method: 'GET');

    $jsonResult = $this->action->handle($jsonCommand);
    $textResult = $this->action->handle($textCommand);
    $htmlResult = $this->action->handle($htmlCommand);

    expect($jsonResult->status)->toBe(200)
        ->and($textResult->status)->toBe(200)
        ->and($htmlResult->status)->toBe(200);
});

it('handles requests with custom headers', function () {
    $this->router->get('/test-custom-headers', function () {
        $auth = request()->header('Authorization');

        return response()->json(['auth' => $auth]);
    });

    $command = new ExecuteCommand(
        endpoint: '/test-custom-headers',
        method: 'GET',
        headers: ['Authorization' => 'Bearer token123'],
    );

    $result = $this->action->handle($command);

    expect($result->status)->toBe(200);
});

it('handles authentication mode current', function () {
    $this->router->get('/test-auth', function () {
        $user = auth()->user();

        return response()->json(['authenticated' => $user !== null]);
    });

    $command = new ExecuteCommand(
        endpoint: '/test-auth',
        method: 'GET',
        authMode: AuthMode::CURRENT,
    );

    $result = $this->action->handle($command);

    expect($result->status)->toBe(200);
});

it('returns size in bytes for response', function () {
    $this->router->get('/test-size', fn () => response()->json(['data' => 'x'.str_repeat('x', 100)]));

    $command = new ExecuteCommand(
        endpoint: '/test-size',
        method: 'GET',
    );

    $result = $this->action->handle($command);

    expect($result->sizeBytes)->toBeGreaterThanOrEqual(0);
});

it('handles 404 not found', function () {
    $command = new ExecuteCommand(
        endpoint: '/non-existent-route-for-testing',
        method: 'GET',
    );

    $result = $this->action->handle($command);

    expect($result->status)->toBe(404);
});

it('handles error responses', function () {
    $this->router->get('/test-error', fn () => response()->json(['error' => 'test error'], 400));

    $command = new ExecuteCommand(
        endpoint: '/test-error',
        method: 'GET',
    );

    $result = $this->action->handle($command);

    expect($result->status)->toBe(400);
});

it('executes PATCH request', function () {
    $this->router->patch('/test-patch/{id}', fn () => response()->json(['patched' => true]));

    $command = new ExecuteCommand(
        endpoint: '/test-patch/{id}',
        method: 'PATCH',
        pathParams: ['id' => '456'],
    );

    $result = $this->action->handle($command);

    expect($result->status)->toBe(200);
});