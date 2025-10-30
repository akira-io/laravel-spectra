<?php

declare(strict_types=1);

namespace Akira\Spectra\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class CookieResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->resource['name'] ?? '',
            'value' => $this->resource['value'] ?? '',
            'encrypted' => $this->resource['encrypted'] ?? false,
            'raw' => $this->resource['raw'] ?? '',
        ];
    }
}
