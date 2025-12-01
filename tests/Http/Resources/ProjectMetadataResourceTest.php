<?php

declare(strict_types=1);

use Akira\Spectra\Http\Resources\ProjectMetadataResource;

it('transforms metadata to array', function () {
    $mockData = (object) ['lastFingerprint' => null];
    $resource = new ProjectMetadataResource($mockData);
    $result = $resource->resolve();

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('project_name')
        ->and($result)->toHaveKey('php_version')
        ->and($result)->toHaveKey('laravel_version')
        ->and($result)->toHaveKey('spectra_version')
        ->and($result)->toHaveKey('last_fingerprint');
});

it('includes project name from config', function () {
    config()->set('app.name', 'MyProject');

    $mockData = (object) ['lastFingerprint' => null];
    $resource = new ProjectMetadataResource($mockData);
    $result = $resource->resolve();

    expect($result['project_name'])->toBe('MyProject');
});

it('includes PHP version', function () {
    $mockData = (object) ['lastFingerprint' => null];
    $resource = new ProjectMetadataResource($mockData);
    $result = $resource->resolve();

    expect($result['php_version'])->toBe(PHP_VERSION);
});

it('includes Laravel version', function () {
    $mockData = (object) ['lastFingerprint' => null];
    $resource = new ProjectMetadataResource($mockData);
    $result = $resource->resolve();

    expect($result['laravel_version'])->toBeString();
});

it('includes spectra version', function () {
    $mockData = (object) ['lastFingerprint' => null];
    $resource = new ProjectMetadataResource($mockData);
    $result = $resource->resolve();

    expect($result['spectra_version'])->toBe('1.0.0');
});

it('includes last fingerprint', function () {
    $mockData = (object) ['lastFingerprint' => 'abc123'];
    $resource = new ProjectMetadataResource($mockData);
    $result = $resource->resolve();

    expect($result['last_fingerprint'])->toBe('abc123');
});

it('handles null last fingerprint', function () {
    $mockData = (object) ['lastFingerprint' => null];
    $resource = new ProjectMetadataResource($mockData);
    $result = $resource->resolve();

    expect($result['last_fingerprint'])->toBeNull();
});

it('returns correct array keys', function () {
    $mockData = (object) ['lastFingerprint' => null];
    $resource = new ProjectMetadataResource($mockData);
    $result = $resource->resolve();

    expect(array_keys($result))->toContain(
        'project_name',
        'php_version',
        'laravel_version',
        'spectra_version',
        'last_fingerprint'
    );
});
