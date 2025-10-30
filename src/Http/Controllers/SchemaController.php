<?php

declare(strict_types=1);

namespace Akira\Spectra\Http\Controllers;

use Akira\Spectra\Actions\BuildSchemaAction;
use Akira\Spectra\Actions\DiscoverRoutesAction;
use Akira\Spectra\Http\Resources\RouteResource;
use Akira\Spectra\Http\Resources\SchemaResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class SchemaController extends Controller
{
    public function __construct(
        private readonly DiscoverRoutesAction $discoverRoutes,
        private readonly BuildSchemaAction $buildSchema
    ) {}

    public function show(): JsonResponse
    {
        $routes = $this->discoverRoutes->handle();
        $schemas = $this->buildSchema->handle();

        return response()->json([
            'routes' => RouteResource::collection($routes),
            'schemas' => SchemaResource::collection($schemas),
        ]);
    }
}
