<?php

declare(strict_types=1);

use Akira\Spectra\Data\SpectraSecurityConfig;

it('creates config from array', function () {
    $data = [
        'strip_headers' => ['Authorization', 'Cookie'],
        'mask_fields' => ['password', 'token'],
        'rate_limit' => ['max' => 100, 'per_minutes' => 2],
    ];

    $config = SpectraSecurityConfig::fromArray($data);

    expect($config->stripHeaders)->toBe(['authorization', 'cookie'])
        ->and($config->maskFields)->toBe(['password', 'token'])
        ->and($config->rateLimitMax)->toBe(100)
        ->and($config->rateLimitPerMinutes)->toBe(2);
});

it('normalizes headers and fields to lowercase', function () {
    $data = [
        'strip_headers' => ['AUTHORIZATION', 'Cookie', 'X-API-KEY'],
        'mask_fields' => ['PASSWORD', 'Token'],
        'rate_limit' => ['max' => 60, 'per_minutes' => 1],
    ];

    $config = SpectraSecurityConfig::fromArray($data);

    expect($config->stripHeaders)->toContain('authorization', 'cookie', 'x-api-key')
        ->and($config->maskFields)->toContain('password', 'token');
});

it('converts to array', function () {
    $config = new SpectraSecurityConfig(
        stripHeaders: ['authorization', 'cookie'],
        maskFields: ['password', 'token'],
        rateLimitMax: 100,
        rateLimitPerMinutes: 2,
    );

    $array = $config->toArray();

    expect($array)->toHaveKey('rate_limit')
        ->and($array['rate_limit']['max'])->toBe(100)
        ->and($array['rate_limit']['per_minutes'])->toBe(2);
});
