<?php

declare(strict_types=1);

use Akira\Spectra\Http\Middleware\VerifyDesktopSignature;
use Illuminate\Http\Request;

it('blocks when desktop integration is disabled', function () {
    $middleware = app(VerifyDesktopSignature::class);

    $request = Request::create('/spectra/desktop/ping', 'GET');
    $request->headers->set('X-Spectra-Token', 'test-key');
    $request->headers->set('X-Spectra-Timestamp', (string) now()->timestamp);
    $request->headers->set('X-Spectra-Nonce', bin2hex(random_bytes(16)));
    $request->headers->set('X-Spectra-Signature', 'test-signature');

    $response = $middleware->handle($request, function () {
        return response('ok');
    });

    expect($response->status())->toBe(403);
});

it('blocks when required headers are missing', function () {
    $middleware = app(VerifyDesktopSignature::class);

    $request = Request::create('/spectra/desktop/ping', 'GET');
    // Missing all headers

    $response = $middleware->handle($request, function () {
        return response('ok');
    });

    expect($response->status())->toBe(403);
});

it('blocks when token does not match public key', function () {
    $middleware = app(VerifyDesktopSignature::class);

    $request = Request::create('/spectra/desktop/ping', 'GET');
    $request->headers->set('X-Spectra-Token', 'wrong-key');
    $request->headers->set('X-Spectra-Timestamp', (string) now()->timestamp);
    $request->headers->set('X-Spectra-Nonce', bin2hex(random_bytes(16)));
    $request->headers->set('X-Spectra-Signature', 'test-signature');

    $response = $middleware->handle($request, function () {
        return response('ok');
    });

    expect($response->status())->toBe(403);
});

it('blocks when signature is invalid', function () {
    $middleware = app(VerifyDesktopSignature::class);

    $request = Request::create('/spectra/desktop/ping', 'GET');
    $request->headers->set('X-Spectra-Token', config('spectra-desktop.public_key'));
    $request->headers->set('X-Spectra-Timestamp', (string) now()->timestamp);
    $request->headers->set('X-Spectra-Nonce', bin2hex(random_bytes(16)));
    $request->headers->set('X-Spectra-Signature', 'invalid-signature-'.bin2hex(random_bytes(32)));

    $response = $middleware->handle($request, function () {
        return response('ok');
    });

    expect($response->status())->toBe(403);
});

it('blocks when desktop is disabled even with valid signature', function () {
    $middleware = app(VerifyDesktopSignature::class);

    $publicKey = config('spectra-desktop.public_key');
    $timestamp = (string) now()->timestamp;
    $nonce = bin2hex(random_bytes(16));
    $bodyJson = '{}';
    $secret = $publicKey.$timestamp.$nonce;
    $signature = hash_hmac('sha256', $bodyJson, $secret, false);

    $request = Request::create('/spectra/desktop/ping', 'GET', [], [], [], ['CONTENT_TYPE' => 'application/json'], $bodyJson);
    $request->headers->set('X-Spectra-Token', $publicKey);
    $request->headers->set('X-Spectra-Timestamp', $timestamp);
    $request->headers->set('X-Spectra-Nonce', $nonce);
    $request->headers->set('X-Spectra-Signature', $signature);

    $response = $middleware->handle($request, function () {
        return response('ok');
    });

    // Should still block because desktop is disabled in test config
    expect($response->status())->toBe(403);
});
