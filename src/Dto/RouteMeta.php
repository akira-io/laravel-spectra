<?php

declare(strict_types=1);

namespace Akira\Spectra\Dto;

final readonly class RouteMeta
{
    /**
     * @param  array<string>  $methods
     * @param  array<string>  $middleware
     * @param  array<ParameterMeta>  $parameters
     * @param  array<string, array{type: string, required: bool, rules: string}>  $bodyParameters
     */
    public function __construct(
        public string $uri,
        public array $methods,
        public ?string $name,
        public ?string $action,
        public array $middleware,
        public array $parameters,
        public array $bodyParameters = [],
    ) {}
}
