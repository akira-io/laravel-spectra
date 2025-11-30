<?php

declare(strict_types=1);

namespace Akira\Spectra\Http\Resources;

use Akira\Spectra\Data\SpectraPayloadVO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property SpectraPayloadVO $resource
 */
final class SpectraPayloadResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->resource->toArray();
    }
}
