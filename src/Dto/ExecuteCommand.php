<?php

declare(strict_types=1);

namespace Akira\Spectra\Dto;

final readonly class ExecuteCommand
{
    /**
     * @param  array<string, mixed>  $pathParams
     * @param  array<string, mixed>  $query
     * @param  array<string, string>  $headers
     */
    public function __construct(
        public string $endpoint,
        public string $method,
        public array $pathParams = [],
        public array $query = [],
        public array $headers = [],
        public mixed $body = null,
        public AuthMode $authMode = AuthMode::CURRENT,
        public ?int $impersonateId = null,
        public ?string $bearerToken = null,
        public ?string $basicUser = null,
        public ?string $basicPass = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            endpoint: $data['endpoint'] ?? '',
            method: mb_strtoupper($data['method'] ?? 'GET'),
            pathParams: $data['path_params'] ?? [],
            query: $data['query'] ?? [],
            headers: $data['headers'] ?? [],
            body: $data['body'] ?? null,
            authMode: AuthMode::from($data['auth_mode'] ?? 'current'),
            impersonateId: $data['impersonate_id'] ?? null,
            bearerToken: $data['bearer_token'] ?? null,
            basicUser: $data['basic_user'] ?? null,
            basicPass: $data['basic_pass'] ?? null,
        );
    }
}
