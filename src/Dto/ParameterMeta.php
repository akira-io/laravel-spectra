<?php

declare(strict_types=1);

namespace Akira\Spectra\Dto;

final readonly class ParameterMeta
{
    public function __construct(
        public string $name,
        public bool $required,
        public ?string $wherePattern = null,
    ) {}
}
