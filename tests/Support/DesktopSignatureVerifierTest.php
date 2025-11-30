<?php

declare(strict_types=1);

use Akira\Spectra\Support\DesktopSignatureVerifier;

it('verifies valid signature', function () {
    $verifier = app(DesktopSignatureVerifier::class);

    $bodyJson = '{"test":"data"}';
    $publicKey = 'test-public-key';
    $timestamp = (string) now()->timestamp;
    $nonce = bin2hex(random_bytes(16));
    $secret = $publicKey.$timestamp.$nonce;
    $signature = hash_hmac('sha256', $bodyJson, $secret, false);

    $result = $verifier->verify($bodyJson, $publicKey, $timestamp, $nonce, $signature, 20);

    expect($result)->toBeTrue();
});

it('rejects invalid signature', function () {
    $verifier = app(DesktopSignatureVerifier::class);

    $bodyJson = '{"test":"data"}';
    $publicKey = 'test-public-key';
    $timestamp = (string) now()->timestamp;
    $nonce = bin2hex(random_bytes(16));
    $invalidSignature = 'invalid-signature-'.bin2hex(random_bytes(32));

    $result = $verifier->verify($bodyJson, $publicKey, $timestamp, $nonce, $invalidSignature, 20);

    expect($result)->toBeFalse();
});

it('rejects signature with old timestamp', function () {
    $verifier = app(DesktopSignatureVerifier::class);

    $bodyJson = '{"test":"data"}';
    $publicKey = 'test-public-key';
    $oldTimestamp = (string) (now()->timestamp - 30);
    $nonce = bin2hex(random_bytes(16));
    $secret = $publicKey.$oldTimestamp.$nonce;
    $signature = hash_hmac('sha256', $bodyJson, $secret, false);

    $result = $verifier->verify($bodyJson, $publicKey, $oldTimestamp, $nonce, $signature, 20);

    expect($result)->toBeFalse();
});

it('rejects duplicate nonce', function () {
    $verifier = app(DesktopSignatureVerifier::class);

    $bodyJson = '{"test":"data"}';
    $publicKey = 'test-public-key';
    $timestamp = (string) now()->timestamp;
    $nonce = 'duplicate-nonce-'.bin2hex(random_bytes(8));
    $secret = $publicKey.$timestamp.$nonce;
    $signature = hash_hmac('sha256', $bodyJson, $secret, false);

    $result1 = $verifier->verify($bodyJson, $publicKey, $timestamp, $nonce, $signature, 20);
    expect($result1)->toBeTrue();

    $result2 = $verifier->verify($bodyJson, $publicKey, $timestamp, $nonce, $signature, 20);
    expect($result2)->toBeFalse();
});

it('rejects invalid timestamp format', function () {
    $verifier = app(DesktopSignatureVerifier::class);

    $bodyJson = '{"test":"data"}';
    $publicKey = 'test-public-key';
    $invalidTimestamp = 'not-a-timestamp';
    $nonce = bin2hex(random_bytes(16));
    $signature = 'test-signature';

    $result = $verifier->verify($bodyJson, $publicKey, $invalidTimestamp, $nonce, $signature, 20);

    expect($result)->toBeFalse();
});

it('handles timestamp conversion errors gracefully', function () {
    $cache = app(Illuminate\Cache\Repository::class);
    $verifier = new DesktopSignatureVerifier($cache);

    $bodyJson = '{"test":"data"}';
    $publicKey = 'test-public-key';
    $timestamp = PHP_INT_MAX;
    $nonce = bin2hex(random_bytes(16));
    $signature = 'test-signature';

    $result = $verifier->verify($bodyJson, $publicKey, (string) $timestamp, $nonce, $signature, 20);

    expect($result)->toBeFalse();
});
