<?php

declare(strict_types=1);

namespace Akira\Spectra\Data;

final readonly class SpectraSecurityConfig
{
    /**
     * @param  array<string>  $stripHeaders
     * @param  array<string>  $maskFields
     */
    public function __construct(
        public array $stripHeaders,
        public array $maskFields,
        public int $rateLimitMax,
        public int $rateLimitPerMinutes,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $rateLimit = (array) ($data['rate_limit'] ?? []);

        return new self(
            stripHeaders: array_map('strtolower', array_values((array) ($data['strip_headers'] ?? []))),
            maskFields: array_map('strtolower', array_values((array) ($data['mask_fields'] ?? []))),
            rateLimitMax: (int) ($rateLimit['max'] ?? 60),
            rateLimitPerMinutes: (int) ($rateLimit['per_minutes'] ?? 1),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'strip_headers' => $this->stripHeaders,
            'mask_fields' => $this->maskFields,
            'rate_limit' => [
                'max' => $this->rateLimitMax,
                'per_minutes' => $this->rateLimitPerMinutes,
            ],
        ];
    }
}
