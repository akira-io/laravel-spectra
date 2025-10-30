<?php

declare(strict_types=1);

namespace Akira\Spectra\Http\Resources;

use Akira\Spectra\Dto\ExecuteResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ExecuteResultResource extends JsonResource
{
    public function __construct(private readonly ExecuteResult $result)
    {
        parent::__construct($result);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'status' => $this->result->status,
            'time_ms' => $this->result->timeMs,
            'size_bytes' => $this->result->sizeBytes,
            'headers' => $this->result->headers,
            'body' => $this->result->body,
        ];
    }
}
