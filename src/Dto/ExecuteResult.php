<?php

declare(strict_types=1);

namespace Akira\Spectra\Dto;

final readonly class ExecuteResult
{
    /**
     * @param  array<string, string|array<string>>  $headers
     */
    public function __construct(
        public int $status,
        public int $timeMs,
        public int $sizeBytes,
        public array $headers,
        public mixed $body,
    ) {}
}
