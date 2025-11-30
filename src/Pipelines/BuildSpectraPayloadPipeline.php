<?php

declare(strict_types=1);

namespace Akira\Spectra\Pipelines;

use Akira\Spectra\Data\SpectraPayloadVO;
use Akira\Spectra\Pipes\CollectMetricsPipe;
use Akira\Spectra\Pipes\CollectModelsPipe;
use Akira\Spectra\Pipes\CollectRoutesPipe;
use Akira\Spectra\Pipes\NormalizePayloadPipe;
use Illuminate\Pipeline\Pipeline;

final readonly class BuildSpectraPayloadPipeline
{
    public function __construct(private Pipeline $pipeline) {}

    public function handle(): SpectraPayloadVO
    {
        $initialPayload = new SpectraPayloadVO(
            routes: [],
            models: [],
            stats: [],
            version: config('app.version', '1.0.0'),
            projectPath: base_path(),
            fingerprint: '',
        );

        return $this->pipeline
            ->send($initialPayload)
            ->through([
                CollectRoutesPipe::class,
                CollectModelsPipe::class,
                CollectMetricsPipe::class,
                NormalizePayloadPipe::class,
            ])
            ->thenReturn();
    }
}
