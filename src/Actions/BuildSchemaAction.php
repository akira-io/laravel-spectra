<?php

declare(strict_types=1);

namespace Akira\Spectra\Actions;

use Akira\Spectra\Dto\SchemaSpec;
use Akira\Spectra\Services\SchemaBuilder;

final readonly class BuildSchemaAction
{
    public function __construct(
        private SchemaBuilder $builder,
        private DiscoverRoutesAction $discoverRoutes
    ) {}

    /**
     * @return array<string, SchemaSpec>
     */
    public function handle(): array
    {
        $routes = $this->discoverRoutes->handle();

        return $this->builder->buildSchemas($routes);
    }
}
