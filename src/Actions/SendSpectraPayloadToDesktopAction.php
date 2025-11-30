<?php

declare(strict_types=1);

namespace Akira\Spectra\Actions;

use Akira\Spectra\Data\SpectraDesktopConfig;
use Akira\Spectra\Data\SpectraPayloadVO;
use Akira\Spectra\Support\ConfigManager;
use Akira\Spectra\Support\SpectraRouteFingerprintStore;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

final readonly class SendSpectraPayloadToDesktopAction
{
    public function __construct(
        private SpectraRouteFingerprintStore $fingerprintStore,
        private ConfigManager $configManager,
    ) {}

    public function handle(SpectraPayloadVO $payload): void
    {
        $desktopConfig = $this->configManager->desktop();

        if (! $desktopConfig->enabled) {
            return;
        }

        if (! $this->fingerprintStore->changed($payload->fingerprint)) {
            return;
        }

        $this->sendWithRetry($payload, $desktopConfig);
    }

    private function sendWithRetry(SpectraPayloadVO $payload, SpectraDesktopConfig $config, int $attempt = 1): void
    {
        try {
            $this->send($payload, $config);
            $this->fingerprintStore->put($payload->fingerprint);
        } catch (Throwable $e) {
            if ($attempt < 3) {
                $this->sendWithRetry($payload, $config, $attempt + 1);

                return;
            }

            Log::error('Failed to send payload to Spectra Desktop after 3 attempts', [
                'error' => $e->getMessage(),
                'fingerprint' => $payload->fingerprint,
            ]);
        }
    }

    private function send(SpectraPayloadVO $payload, SpectraDesktopConfig $config): void
    {
        $bodyJson = json_encode($payload->toArray(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        $timestamp = (string) now()->timestamp;
        $nonce = bin2hex(random_bytes(16));
        $secret = $config->publicKey.$timestamp.$nonce;
        $signature = hash_hmac('sha256', $bodyJson, $secret, false);

        $response = Http::timeout(10)
            ->withHeaders([
                'X-Spectra-Token' => $config->publicKey,
                'X-Spectra-Timestamp' => $timestamp,
                'X-Spectra-Nonce' => $nonce,
                'X-Spectra-Signature' => $signature,
                'Content-Type' => 'application/json',
            ])
            ->post($config->desktopUrl.'/ingest', $payload->toArray());

        if (! $response->successful()) {
            throw new RuntimeException("Desktop responded with status {$response->status()}");
        }
    }
}
