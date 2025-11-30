<?php

declare(strict_types=1);

namespace Akira\Spectra\Actions;

use JsonException;

final readonly class SpectraRouteFingerprintAction
{
    /**
     * @param  array<string, mixed>  $routes
     *
     * @throws JsonException
     */
    public function handle(array $routes): string
    {
        $sorted = $this->sortRoutes($routes);
        $json = json_encode($sorted, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);

        return hash('sha256', $json);
    }

    /**
     * @param  array<string, mixed>  $routes
     * @return array<string, mixed>
     */
    private function sortRoutes(array $routes): array
    {
        ksort($routes);

        foreach ($routes as $key => $value) {
            if (is_array($value)) {
                $routes[$key] = $this->sortRoutes($value);
            }
        }

        return $routes;
    }
}
