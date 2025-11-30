<?php

declare(strict_types=1);

namespace Akira\Spectra\Data;

final readonly class SpectraPayloadVO
{
    /**
     * @param  array<string, mixed>  $routes
     * @param  array<string, mixed>  $models
     * @param  array<string, mixed>  $stats
     */
    public function __construct(
        public array $routes,
        public array $models,
        public array $stats,
        public string $version,
        public string $projectPath,
        public string $fingerprint,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'routes' => $this->routes,
            'models' => $this->models,
            'stats' => $this->stats,
            'version' => $this->version,
            'project_path' => $this->projectPath,
            'fingerprint' => $this->fingerprint,
        ];
    }
}
