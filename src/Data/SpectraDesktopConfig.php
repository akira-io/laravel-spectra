<?php

declare(strict_types=1);

namespace Akira\Spectra\Data;

final readonly class SpectraDesktopConfig
{
    public function __construct(
        public bool $enabled,
        public string $desktopUrl,
        public string $publicKey,
        public int $maxDrift,
    ) {}

    /**
     * Create from array with strict typing
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            enabled: (bool) ($data['enabled'] ?? false),
            desktopUrl: (string) ($data['desktop_url'] ?? ''),
            publicKey: (string) ($data['public_key'] ?? ''),
            maxDrift: (int) ($data['max_drift'] ?? 20),
        );
    }

    public static function fromConfig(): self
    {
        return self::fromArray(config('spectra-desktop', []));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'enabled' => $this->enabled,
            'desktop_url' => $this->desktopUrl,
            'public_key' => $this->publicKey,
            'max_drift' => $this->maxDrift,
        ];
    }
}
