<?php

declare(strict_types=1);

namespace Akira\Spectra\Support;

use Illuminate\Cache\Repository as CacheRepository;
use Throwable;

final readonly class DesktopSignatureVerifier
{
    public function __construct(private CacheRepository $cache) {}

    public function verify(
        string $bodyJson,
        string $publicKey,
        string $timestamp,
        string $nonce,
        string $signature,
        int $maxDrift,
    ): bool {
        if (! $this->verifyTimestampDrift($timestamp, $maxDrift)) {
            return false;
        }

        if (! $this->verifyNonceUniqueness($nonce)) {
            return false;
        }

        if (! $this->verifySignature($bodyJson, $publicKey, $timestamp, $nonce, $signature)) {
            return false;
        }

        return true;
    }

    private function verifyTimestampDrift(string $timestamp, int $maxDrift): bool
    {
        try {
            $requestTime = (int) $timestamp;
            $currentTime = now()->timestamp;
            $drift = abs($currentTime - $requestTime);

            return $drift <= $maxDrift;
        } catch (Throwable) {
            return false;
        }
    }

    private function verifyNonceUniqueness(string $nonce): bool
    {
        $cacheKey = "spectra:nonce:{$nonce}";

        if ($this->cache->has($cacheKey)) {
            return false;
        }

        $this->cache->put($cacheKey, true, 3600);

        return true;
    }

    private function verifySignature(
        string $bodyJson,
        string $publicKey,
        string $timestamp,
        string $nonce,
        string $signature,
    ): bool {
        $secret = $publicKey.$timestamp.$nonce;
        $computed = hash_hmac('sha256', $bodyJson, $secret, false);

        return hash_equals($computed, $signature);
    }
}
