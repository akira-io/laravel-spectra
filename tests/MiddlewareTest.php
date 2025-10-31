<?php

declare(strict_types=1);

use Akira\Spectra\Http\Middleware\EnsureSpectraEnabled;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

it('blocks access when spectra is disabled', function () {
    config(['spectra.enabled' => false]);

    $middleware = new EnsureSpectraEnabled;
    $request = Request::create('/spectra');

    $middleware->handle($request, fn () => response('OK'));
})->throws(NotFoundHttpException::class);

it('blocks access in production when only_local is true', function () {
    config(['spectra.enabled' => true, 'spectra.only_local' => true]);
    app()->detectEnvironment(fn () => 'production');

    $middleware = new EnsureSpectraEnabled;
    $request = Request::create('/spectra');

    $middleware->handle($request, fn () => response('OK'));
})->throws(NotFoundHttpException::class);

it('allows access in local environment', function () {
    config(['spectra.enabled' => true, 'spectra.only_local' => true]);
    app()->detectEnvironment(fn () => 'local');

    $middleware = new EnsureSpectraEnabled;
    $request = Request::create('/spectra');

    $response = $middleware->handle($request, fn () => response('OK'));

    expect($response->getContent())->toBe('OK');
});
