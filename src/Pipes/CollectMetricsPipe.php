<?php

declare(strict_types=1);

namespace Akira\Spectra\Pipes;

use Akira\Spectra\Data\SpectraPayloadVO;
use Closure;

final readonly class CollectMetricsPipe
{
    /**
     * @param  Closure(SpectraPayloadVO): SpectraPayloadVO  $next
     */
    public function __invoke(SpectraPayloadVO $payload, Closure $next): SpectraPayloadVO
    {
        $stats = [
            'total_routes' => count($payload->routes),
            'total_models' => count($payload->models),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'timestamp' => now()->toIso8601String(),
        ];

        return $next(new SpectraPayloadVO(
            routes: $payload->routes,
            models: $payload->models,
            stats: $stats,
            version: $payload->version,
            projectPath: $payload->projectPath,
            fingerprint: $payload->fingerprint,
        ));
    }
}
