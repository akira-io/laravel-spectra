<?php

declare(strict_types=1);

namespace Akira\Spectra\Data;

final readonly class SpectraGeneralConfig
{
    public function __construct(
        public bool $enabled,
        public bool $onlyLocal,
        public bool $requireAuth,
        public string $guard,
        public string $impersonationGate,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            enabled: (bool) ($data['enabled'] ?? false),
            onlyLocal: (bool) ($data['only_local'] ?? true),
            requireAuth: (bool) ($data['require_auth'] ?? true),
            guard: (string) ($data['guard'] ?? 'web'),
            impersonationGate: (string) ($data['impersonation_gate'] ?? 'use-spectra'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'enabled' => $this->enabled,
            'only_local' => $this->onlyLocal,
            'require_auth' => $this->requireAuth,
            'guard' => $this->guard,
            'impersonation_gate' => $this->impersonationGate,
        ];
    }
}
