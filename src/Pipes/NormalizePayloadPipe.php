<?php

declare(strict_types=1);

namespace Akira\Spectra\Pipes;

use Akira\Spectra\Actions\SpectraRouteFingerprintAction;
use Akira\Spectra\Data\SpectraPayloadVO;
use Closure;
use JsonException;

final readonly class NormalizePayloadPipe
{
    public function __construct(private SpectraRouteFingerprintAction $fingerprintAction) {}

    /**
     * @param  Closure(SpectraPayloadVO): SpectraPayloadVO  $next
     *
     * @throws JsonException
     */
    public function __invoke(SpectraPayloadVO $payload, Closure $next): SpectraPayloadVO
    {
        $fingerprint = $this->fingerprintAction->handle($payload->routes);

        return $next(new SpectraPayloadVO(
            routes: $payload->routes,
            models: $payload->models,
            stats: $payload->stats,
            version: $payload->version,
            projectPath: $payload->projectPath,
            fingerprint: $fingerprint,
        ));
    }
}
