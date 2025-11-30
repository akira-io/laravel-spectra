<?php

declare(strict_types=1);

namespace Akira\Spectra\Http\Middleware;

use Akira\Spectra\Support\ConfigManager;
use Akira\Spectra\Support\DesktopSignatureVerifier;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class VerifyDesktopSignature
{
    public function __construct(
        private ConfigManager $configManager,
        private DesktopSignatureVerifier $verifier,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $desktopConfig = $this->configManager->desktop();

        if (! $desktopConfig->enabled) {
            return response('Desktop integration disabled', 403);
        }

        $token = $request->header('X-Spectra-Token');
        $timestamp = $request->header('X-Spectra-Timestamp');
        $nonce = $request->header('X-Spectra-Nonce');
        $signature = $request->header('X-Spectra-Signature');

        if (! $token || ! $timestamp || ! $nonce || ! $signature) {
            return response('Missing signature headers', 403);
        }

        if ($token !== $desktopConfig->publicKey) {
            return response('Invalid token', 403);
        }

        $bodyJson = $request->getContent();

        if (! $this->verifier->verify(
            $bodyJson,
            $desktopConfig->publicKey,
            $timestamp,
            $nonce,
            $signature,
            $desktopConfig->maxDrift
        )) {
            return response('Invalid signature', 403);
        }

        return $next($request);
    }
}
