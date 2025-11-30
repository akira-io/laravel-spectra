<?php

declare(strict_types=1);

namespace Akira\Spectra\Data;

final readonly class SpectraRouteFilterConfig
{
    /**
     * @param  array<string>  $includeRoutes
     * @param  array<string>  $excludeRoutes
     */
    public function __construct(
        public array $includeRoutes,
        public array $excludeRoutes,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            includeRoutes: array_values((array) ($data['include_routes'] ?? [])),
            excludeRoutes: array_values((array) ($data['exclude_routes'] ?? [])),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'include_routes' => $this->includeRoutes,
            'exclude_routes' => $this->excludeRoutes,
        ];
    }
}
