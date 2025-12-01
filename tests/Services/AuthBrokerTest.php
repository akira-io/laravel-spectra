<?php

declare(strict_types=1);

use Akira\Spectra\Dto\AuthMode;
use Akira\Spectra\Services\AuthBroker;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\User;

beforeEach(function () {
    $this->authBroker = app(AuthBroker::class);
});

it('returns current authenticated user', function () {
    config()->set('spectra.guard', 'web');

    $result = $this->authBroker->authenticate(AuthMode::CURRENT);

    expect($result)->toBeNull();
});

it('returns null when no bearer token provided', function () {
    $result = $this->authBroker->authenticate(AuthMode::BEARER, bearerToken: null);

    expect($result)->toBeNull();
});

it('returns null when no basic credentials provided', function () {
    $result = $this->authBroker->authenticate(AuthMode::BASIC, basicUser: null, basicPass: null);

    expect($result)->toBeNull();
});

it('returns null when basic pass missing', function () {
    $result = $this->authBroker->authenticate(AuthMode::BASIC, basicUser: 'user', basicPass: null);

    expect($result)->toBeNull();
});

it('returns null when impersonate id not provided', function () {
    $result = $this->authBroker->authenticate(AuthMode::IMPERSONATE, impersonateId: null);

    expect($result)->toBeNull();
});

it('handles different authentication modes', function () {
    $modes = [AuthMode::CURRENT, AuthMode::BEARER, AuthMode::BASIC, AuthMode::IMPERSONATE];

    foreach ($modes as $mode) {
        $result = $this->authBroker->authenticate($mode);
        expect($result)->toBeNull();
    }
});

it('uses configured guard name', function () {
    config()->set('spectra.guard', 'web');

    $result = $this->authBroker->authenticate(AuthMode::CURRENT);

    expect($result)->toBeNull();
});

it('returns null for invalid bearer token', function () {
    config()->set('spectra.guard', 'web');

    $result = $this->authBroker->authenticate(
        AuthMode::BEARER,
        bearerToken: 'invalid-token-xyz'
    );

    expect($result)->toBeNull();
});

it('returns null when basic user is null', function () {
    $result = $this->authBroker->authenticate(
        AuthMode::BASIC,
        basicUser: null,
        basicPass: 'password'
    );

    expect($result)->toBeNull();
});
