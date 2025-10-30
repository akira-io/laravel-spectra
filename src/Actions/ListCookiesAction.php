<?php

declare(strict_types=1);

namespace Akira\Spectra\Actions;

use Akira\Spectra\Services\CookieInspector;

final readonly class ListCookiesAction
{
    public function __construct(private CookieInspector $inspector) {}

    /**
     * @return array<array<string, mixed>>
     */
    public function handle(): array
    {
        return $this->inspector->inspect();
    }
}
