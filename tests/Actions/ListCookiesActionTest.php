<?php

declare(strict_types=1);

use Akira\Spectra\Actions\ListCookiesAction;

beforeEach(function () {
    $this->action = app(ListCookiesAction::class);
});

it('returns array of cookies', function () {
    $cookies = $this->action->handle();

    expect($cookies)->toBeArray();
});

it('returns array of cookie arrays', function () {
    $cookies = $this->action->handle();

    if (count($cookies) > 0) {
        foreach ($cookies as $cookie) {
            expect($cookie)->toBeArray();
        }
    } else {
        expect(true)->toBeTrue();
    }
});

it('includes cookie name in result', function () {
    $_COOKIE['test_cookie'] = 'test_value';

    $cookies = $this->action->handle();

    $testCookie = collect($cookies)->firstWhere('name', 'test_cookie');

    expect($testCookie)->not->toBeNull()
        ->and($testCookie['name'])->toBe('test_cookie');

    unset($_COOKIE['test_cookie']);
});

it('includes cookie value in result', function () {
    $_COOKIE['my_cookie'] = 'my_value';

    $cookies = $this->action->handle();

    $myCookie = collect($cookies)->firstWhere('name', 'my_cookie');

    expect($myCookie)->not->toBeNull()
        ->and($myCookie)->toHaveKey('value');

    unset($_COOKIE['my_cookie']);
});

it('includes encrypted flag in result', function () {
    $_COOKIE['plain_cookie'] = 'plain_value';

    $cookies = $this->action->handle();

    $plainCookie = collect($cookies)->firstWhere('name', 'plain_cookie');

    expect($plainCookie)->not->toBeNull()
        ->and($plainCookie)->toHaveKey('encrypted')
        ->and(is_bool($plainCookie['encrypted']))->toBeTrue();

    unset($_COOKIE['plain_cookie']);
});

it('includes raw cookie value in result', function () {
    $_COOKIE['raw_cookie'] = 'raw_value';

    $cookies = $this->action->handle();

    $rawCookie = collect($cookies)->firstWhere('name', 'raw_cookie');

    expect($rawCookie)->not->toBeNull()
        ->and($rawCookie)->toHaveKey('raw')
        ->and($rawCookie['raw'])->toBe('raw_value');

    unset($_COOKIE['raw_cookie']);
});

it('detects plain text cookies as not encrypted', function () {
    $_COOKIE['plain_text'] = 'plain_text_value';

    $cookies = $this->action->handle();

    $plainCookie = collect($cookies)->firstWhere('name', 'plain_text');

    expect($plainCookie)->not->toBeNull()
        ->and($plainCookie['encrypted'])->toBeFalse();

    unset($_COOKIE['plain_text']);
});

it('handles multiple cookies', function () {
    $_COOKIE['cookie1'] = 'value1';
    $_COOKIE['cookie2'] = 'value2';
    $_COOKIE['cookie3'] = 'value3';

    $cookies = $this->action->handle();

    $names = collect($cookies)->pluck('name')->toArray();

    expect($names)->toContain('cookie1', 'cookie2', 'cookie3');

    unset($_COOKIE['cookie1']);
    unset($_COOKIE['cookie2']);
    unset($_COOKIE['cookie3']);
});

it('includes all required cookie properties', function () {
    $_COOKIE['complete_cookie'] = 'complete_value';

    $cookies = $this->action->handle();

    $cookie = collect($cookies)->firstWhere('name', 'complete_cookie');

    expect($cookie)->not->toBeNull()
        ->and($cookie)->toHaveKey('name')
        ->and($cookie)->toHaveKey('value')
        ->and($cookie)->toHaveKey('encrypted')
        ->and($cookie)->toHaveKey('raw');

    unset($_COOKIE['complete_cookie']);
});

it('handles empty cookies', function () {
    $_COOKIE = [];

    $cookies = $this->action->handle();

    expect($cookies)->toBeArray();
});

it('handles cookies with special characters', function () {
    $_COOKIE['special'] = 'value-with_special.chars';

    $cookies = $this->action->handle();

    $specialCookie = collect($cookies)->firstWhere('name', 'special');

    expect($specialCookie)->not->toBeNull()
        ->and($specialCookie['name'])->toBe('special');

    unset($_COOKIE['special']);
});

it('handles cookies with numeric values', function () {
    $_COOKIE['numeric'] = '12345';

    $cookies = $this->action->handle();

    $numericCookie = collect($cookies)->firstWhere('name', 'numeric');

    expect($numericCookie)->not->toBeNull()
        ->and($numericCookie['raw'])->toBe('12345');

    unset($_COOKIE['numeric']);
});

it('returns result as array of arrays with mixed keys', function () {
    $_COOKIE['test'] = 'value';

    $cookies = $this->action->handle();

    if (count($cookies) > 0) {
        expect($cookies[0])->toBeArray()
            ->and($cookies[0])->toHaveKey('name')
            ->and($cookies[0])->toHaveKey('value')
            ->and($cookies[0])->toHaveKey('encrypted')
            ->and($cookies[0])->toHaveKey('raw');
    }

    unset($_COOKIE['test']);
});

it('handles cookies with empty values', function () {
    $_COOKIE['empty'] = '';

    $cookies = $this->action->handle();

    $emptyCookie = collect($cookies)->firstWhere('name', 'empty');

    expect($emptyCookie)->not->toBeNull();

    unset($_COOKIE['empty']);
});

it('preserves cookie name exactly as stored', function () {
    $_COOKIE['CaSeSensitive'] = 'value';

    $cookies = $this->action->handle();

    $cookie = collect($cookies)->firstWhere('name', 'CaSeSensitive');

    expect($cookie)->not->toBeNull()
        ->and($cookie['name'])->toBe('CaSeSensitive');

    unset($_COOKIE['CaSeSensitive']);
});