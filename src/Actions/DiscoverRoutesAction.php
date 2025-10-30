<?php

declare(strict_types=1);

namespace Akira\Spectra\Actions;

use Akira\Spectra\Dto\RouteMeta;
use Akira\Spectra\Services\RouteScanner;

final readonly class DiscoverRoutesAction
{
    public function __construct(private RouteScanner $scanner) {}

    /**
     * @return array<RouteMeta>
     */
    public function handle(): array
    {
        return $this->scanner->scan();
    }
}
