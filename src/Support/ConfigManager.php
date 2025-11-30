<?php

declare(strict_types=1);

namespace Akira\Spectra\Support;

use Akira\Spectra\Data\SpectraDesktopConfig;
use Akira\Spectra\Data\SpectraGeneralConfig;
use Akira\Spectra\Data\SpectraRouteFilterConfig;
use Akira\Spectra\Data\SpectraSecurityConfig;

final readonly class ConfigManager
{
    private SpectraGeneralConfig $general;

    private SpectraRouteFilterConfig $routeFilter;

    private SpectraSecurityConfig $security;

    private SpectraDesktopConfig $desktop;

    public function __construct()
    {
        $this->general = SpectraGeneralConfig::fromArray(config('spectra', []));
        $this->routeFilter = SpectraRouteFilterConfig::fromArray(config('spectra', []));
        $this->security = SpectraSecurityConfig::fromArray(config('spectra', []));
        $this->desktop = SpectraDesktopConfig::fromConfig();
    }

    public static function make(): self
    {
        return new self();
    }

    public function general(): SpectraGeneralConfig
    {
        return $this->general;
    }

    public function routeFilter(): SpectraRouteFilterConfig
    {
        return $this->routeFilter;
    }

    public function security(): SpectraSecurityConfig
    {
        return $this->security;
    }

    public function desktop(): SpectraDesktopConfig
    {
        return $this->desktop;
    }

    public function isEnabled(): bool
    {
        return $this->general->enabled;
    }

    public function isOnlyLocal(): bool
    {
        return $this->general->onlyLocal;
    }

    public function requiresAuth(): bool
    {
        return $this->general->requireAuth;
    }

    public function getGuard(): string
    {
        return $this->general->guard;
    }

    public function getImpersonationGate(): string
    {
        return $this->general->impersonationGate;
    }

    /**
     * @return array<string>
     */
    public function getIncludeRoutes(): array
    {
        return $this->routeFilter->includeRoutes;
    }

    /**
     * @return array<string>
     */
    public function getExcludeRoutes(): array
    {
        return $this->routeFilter->excludeRoutes;
    }

    /**
     * @return array<string>
     */
    public function getStripHeaders(): array
    {
        return $this->security->stripHeaders;
    }

    /**
     * @return array<string>
     */
    public function getMaskFields(): array
    {
        return $this->security->maskFields;
    }

    public function getRateLimitMax(): int
    {
        return $this->security->rateLimitMax;
    }

    public function getRateLimitPerMinutes(): int
    {
        return $this->security->rateLimitPerMinutes;
    }

    public function isDesktopEnabled(): bool
    {
        return $this->desktop->enabled;
    }

    public function getDesktopUrl(): string
    {
        return $this->desktop->desktopUrl;
    }

    public function getDesktopPublicKey(): string
    {
        return $this->desktop->publicKey;
    }

    public function getDesktopMaxDrift(): int
    {
        return $this->desktop->maxDrift;
    }
}
