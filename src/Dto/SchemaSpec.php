<?php

declare(strict_types=1);

namespace Akira\Spectra\Dto;

final readonly class SchemaSpec
{
    /**
     * @param  array<string, mixed>  $pathSchema
     * @param  array<string, mixed>  $querySchema
     * @param  array<string, mixed>  $bodySchema
     * @param  array<string, mixed>  $headersSchema
     */
    public function __construct(
        public string $routeIdentifier,
        public string $method,
        public array $pathSchema,
        public array $querySchema,
        public array $bodySchema,
        public array $headersSchema,
    ) {}
}
