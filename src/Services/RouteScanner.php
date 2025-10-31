<?php

declare(strict_types=1);

namespace Akira\Spectra\Services;

use Akira\Spectra\Dto\ParameterMeta;
use Akira\Spectra\Dto\RouteMeta;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;

final readonly class RouteScanner
{
    public function __construct(private Router $router) {}

    /**
     * @return array<RouteMeta>
     */
    public function scan(): array
    {
        $routes = [];

        foreach ($this->router->getRoutes() as $route) {
            if ($this->shouldSkip($route)) {
                continue;
            }

            $routes[] = $this->mapRoute($route);
        }

        return $routes;
    }

    private function shouldSkip(Route $route): bool
    {
        $uri = $route->uri();

        // Check exclude patterns
        $excludeRoutes = config('spectra.exclude_routes', [
            'spectra',
            '_ignition',
            'sanctum',
            'telescope',
            'horizon',
            'pulse',
        ]);

        foreach ($excludeRoutes as $pattern) {
            if (str_starts_with($uri, $pattern)) {
                return true;
            }
        }

        // Check include patterns
        $includeRoutes = config('spectra.include_routes', ['api/*']);
        
        if (empty($includeRoutes)) {
            return false; // Include all if no patterns specified
        }

        foreach ($includeRoutes as $pattern) {
            // Convert wildcard pattern to regex
            $regex = str_replace(['*', '/'], ['.*', '\/'], $pattern);
            if (preg_match('/^' . $regex . '$/', $uri)) {
                return false; // Include this route
            }
        }

        return true; // Exclude by default if include patterns are specified
    }

    private function mapRoute(Route $route): RouteMeta
    {
        return new RouteMeta(
            uri: $route->uri(),
            methods: $route->methods(),
            name: $route->getName(),
            action: $route->getActionName(),
            middleware: $route->gatherMiddleware(),
            parameters: $this->extractParameters($route),
        );
    }

    /**
     * @return array<ParameterMeta>
     */
    private function extractParameters(Route $route): array
    {
        $parameters = [];

        foreach ($route->parameterNames() as $name) {
            $parameters[] = new ParameterMeta(
                name: $name,
                required: ! str_ends_with($name, '?'),
                wherePattern: $route->wheres[$name] ?? null,
            );
        }

        return $parameters;
    }
}
