<?php

declare(strict_types=1);

use Akira\Spectra\Http\Resources\CookieResource;

it('transforms cookie data to array', function () {
    $cookieData = [
        'name' => 'test-cookie',
        'value' => 'test-value',
        'encrypted' => false,
        'raw' => 'test-raw',
    ];

    $resource = new CookieResource($cookieData);
    $result = $resource->resolve();

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('name')
        ->and($result)->toHaveKey('value')
        ->and($result)->toHaveKey('encrypted')
        ->and($result)->toHaveKey('raw');
});

it('includes cookie name', function () {
    $cookieData = ['name' => 'my-cookie', 'value' => '', 'encrypted' => false, 'raw' => ''];

    $resource = new CookieResource($cookieData);
    $result = $resource->resolve();

    expect($result['name'])->toBe('my-cookie');
});

it('includes cookie value', function () {
    $cookieData = ['name' => '', 'value' => 'my-value', 'encrypted' => false, 'raw' => ''];

    $resource = new CookieResource($cookieData);
    $result = $resource->resolve();

    expect($result['value'])->toBe('my-value');
});

it('includes encrypted flag', function () {
    $cookieData = ['name' => '', 'value' => '', 'encrypted' => true, 'raw' => ''];

    $resource = new CookieResource($cookieData);
    $result = $resource->resolve();

    expect($result['encrypted'])->toBeTrue();
});

it('includes raw value', function () {
    $cookieData = ['name' => '', 'value' => '', 'encrypted' => false, 'raw' => 'raw-value'];

    $resource = new CookieResource($cookieData);
    $result = $resource->resolve();

    expect($result['raw'])->toBe('raw-value');
});

it('defaults missing name to empty string', function () {
    $cookieData = ['value' => 'value', 'encrypted' => false, 'raw' => ''];

    $resource = new CookieResource($cookieData);
    $result = $resource->resolve();

    expect($result['name'])->toBe('');
});

it('defaults missing value to empty string', function () {
    $cookieData = ['name' => 'name', 'encrypted' => false, 'raw' => ''];

    $resource = new CookieResource($cookieData);
    $result = $resource->resolve();

    expect($result['value'])->toBe('');
});

it('defaults missing encrypted to false', function () {
    $cookieData = ['name' => '', 'value' => '', 'raw' => ''];

    $resource = new CookieResource($cookieData);
    $result = $resource->resolve();

    expect($result['encrypted'])->toBeFalse();
});

it('defaults missing raw to empty string', function () {
    $cookieData = ['name' => '', 'value' => '', 'encrypted' => false];

    $resource = new CookieResource($cookieData);
    $result = $resource->resolve();

    expect($result['raw'])->toBe('');
});
