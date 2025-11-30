<?php

declare(strict_types=1);

use Akira\Spectra\Pipelines\BuildSpectraPayloadPipeline;

it('builds payload through pipeline', function () {
    $pipeline = app(BuildSpectraPayloadPipeline::class);
    $payload = $pipeline->handle();

    expect($payload)->not->toBeNull()
        ->and($payload->routes)->toBeArray()
        ->and($payload->models)->toBeArray()
        ->and($payload->stats)->toBeArray()
        ->and($payload->version)->toBeString()
        ->and($payload->projectPath)->toBeString()
        ->and($payload->fingerprint)->toBeString();
});

it('payload has all required stats', function () {
    $pipeline = app(BuildSpectraPayloadPipeline::class);
    $payload = $pipeline->handle();

    expect($payload->stats)->toHaveKeys(['total_routes', 'total_models', 'php_version', 'laravel_version', 'timestamp']);
});

it('fingerprint is sha256 hash', function () {
    $pipeline = app(BuildSpectraPayloadPipeline::class);
    $payload = $pipeline->handle();

    expect($payload->fingerprint)->toMatch('/^[a-f0-9]{64}$/');
});
