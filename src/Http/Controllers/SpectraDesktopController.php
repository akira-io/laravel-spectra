<?php

declare(strict_types=1);

namespace Akira\Spectra\Http\Controllers;

use Akira\Spectra\Actions\SendSpectraPayloadToDesktopAction;
use Akira\Spectra\Http\Resources\ProjectMetadataResource;
use Akira\Spectra\Http\Resources\SpectraPayloadResource;
use Akira\Spectra\Pipelines\BuildSpectraPayloadPipeline;
use Akira\Spectra\Support\SpectraRouteFingerprintStore;
use Illuminate\Http\JsonResponse;

final readonly class SpectraDesktopController
{
    public function __construct(
        private BuildSpectraPayloadPipeline $payloadPipeline,
        private SpectraRouteFingerprintStore $fingerprintStore,
        private SendSpectraPayloadToDesktopAction $sendAction,
    ) {}

    public function ping(): ProjectMetadataResource
    {
        return new ProjectMetadataResource((object) [
            'lastFingerprint' => $this->fingerprintStore->get(),
        ]);
    }

    public function export(): SpectraPayloadResource
    {
        $payload = $this->payloadPipeline->handle();

        return new SpectraPayloadResource($payload);
    }

    public function forceSync(): JsonResponse
    {
        $payload = $this->payloadPipeline->handle();

        $this->sendAction->handle($payload);

        return response()->json(['synced' => true]);
    }
}
