<?php

declare(strict_types=1);

namespace Akira\Spectra\Pipes;

use Akira\Spectra\Data\SpectraPayloadVO;
use Akira\Spectra\Dto\RouteMeta;
use Akira\Spectra\Services\RouteScanner;
use Closure;

final readonly class CollectRoutesPipe
{
    public function __construct(private RouteScanner $scanner) {}

    /**
     * @param  Closure(SpectraPayloadVO): SpectraPayloadVO  $next
     */
    public function __invoke(SpectraPayloadVO $payload, Closure $next): SpectraPayloadVO
    {
        $routes = $this->scanner->scan();
        $routesArray = array_map(
            fn ($route) => $this->serializeRoute($route),
            $routes
        );

        return $next(new SpectraPayloadVO(
            routes: $routesArray,
            models: $payload->models,
            stats: $payload->stats,
            version: $payload->version,
            projectPath: $payload->projectPath,
            fingerprint: $payload->fingerprint,
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeRoute(mixed $route): array
    {
        if ($route instanceof RouteMeta) {
            return [
                'uri' => $route->uri,
                'methods' => $route->methods,
                'name' => $route->name,
                'action' => $route->action,
                'middleware' => $route->middleware,
                'parameters' => $route->parameters,
                'bodyParameters' => $route->bodyParameters,
            ];
        }

        return (array) $route;
    }
}
