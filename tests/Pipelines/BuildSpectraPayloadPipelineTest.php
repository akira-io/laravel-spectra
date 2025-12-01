<?php

declare(strict_types=1);

use Akira\Spectra\Data\SpectraPayloadVO;
use Akira\Spectra\Pipelines\BuildSpectraPayloadPipeline;

it('returns spectra payload value object', function () {
    $pipeline = app(BuildSpectraPayloadPipeline::class);

    $result = $pipeline->handle();

    expect($result)->toBeInstanceOf(SpectraPayloadVO::class);
});

it('payload includes version', function () {
    $pipeline = app(BuildSpectraPayloadPipeline::class);

    $result = $pipeline->handle();

    expect($result->version)->toBeString();
});

it('payload includes project path', function () {
    $pipeline = app(BuildSpectraPayloadPipeline::class);

    $result = $pipeline->handle();

    expect($result->projectPath)->toBeString();
    expect($result->projectPath)->toBe(base_path());
});

it('payload includes fingerprint', function () {
    $pipeline = app(BuildSpectraPayloadPipeline::class);

    $result = $pipeline->handle();

    expect($result->fingerprint)->toBeString();
});

it('payload includes routes array', function () {
    $pipeline = app(BuildSpectraPayloadPipeline::class);

    $result = $pipeline->handle();

    expect($result->routes)->toBeArray();
});

it('payload includes models array', function () {
    $pipeline = app(BuildSpectraPayloadPipeline::class);

    $result = $pipeline->handle();

    expect($result->models)->toBeArray();
});

it('payload includes stats array', function () {
    $pipeline = app(BuildSpectraPayloadPipeline::class);

    $result = $pipeline->handle();

    expect($result->stats)->toBeArray();
});

it('runs through all pipes', function () {
    $pipeline = app(BuildSpectraPayloadPipeline::class);

    $result = $pipeline->handle();

    expect($result)->toBeInstanceOf(SpectraPayloadVO::class)
        ->and($result->routes)->toBeArray()
        ->and($result->models)->toBeArray()
        ->and($result->stats)->toBeArray();
});

it('collects application routes as array', function () {
    $pipeline = app(BuildSpectraPayloadPipeline::class);

    $result = $pipeline->handle();

    expect($result->routes)->toBeArray();
});
