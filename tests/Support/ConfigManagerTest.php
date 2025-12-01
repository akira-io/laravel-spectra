<?php

declare(strict_types=1);

use Akira\Spectra\Support\ConfigManager;

it('loads general config correctly', function () {
    $configManager = ConfigManager::make();

    expect($configManager->general())->not->toBeNull()
        ->and($configManager->isEnabled())->toBeBool();
});

it('loads route filter config', function () {
    $configManager = ConfigManager::make();

    expect($configManager->routeFilter()->includeRoutes)->toBeArray()
        ->and($configManager->routeFilter()->excludeRoutes)->toBeArray();
});

it('loads security config', function () {
    $configManager = ConfigManager::make();

    expect($configManager->security()->stripHeaders)->toBeArray()
        ->and($configManager->security()->maskFields)->toBeArray()
        ->and($configManager->security()->rateLimitMax)->toBeInt()
        ->and($configManager->security()->rateLimitPerMinutes)->toBeInt();
});

it('loads desktop config', function () {
    $configManager = ConfigManager::make();

    expect($configManager->desktop())->not->toBeNull()
        ->and($configManager->desktop()->enabled)->toBeBool();
});

it('provides helper methods for general config', function () {
    $configManager = ConfigManager::make();

    expect($configManager->isEnabled())->toBeBool()
        ->and($configManager->isOnlyLocal())->toBeBool()
        ->and($configManager->requiresAuth())->toBeBool()
        ->and($configManager->getGuard())->toBeString();
});

it('provides helper methods for route filters', function () {
    $configManager = ConfigManager::make();

    expect($configManager->getIncludeRoutes())->toBeArray()
        ->and($configManager->getExcludeRoutes())->toBeArray();
});

it('provides helper methods for security config', function () {
    $configManager = ConfigManager::make();

    expect($configManager->getStripHeaders())->toBeArray()
        ->and($configManager->getMaskFields())->toBeArray()
        ->and($configManager->getRateLimitMax())->toBeInt()
        ->and($configManager->getRateLimitPerMinutes())->toBeInt();
});

it('provides helper methods for desktop config', function () {
    $configManager = ConfigManager::make();

    expect($configManager->isDesktopEnabled())->toBeBool()
        ->and($configManager->getDesktopUrl())->toBeString()
        ->and($configManager->getDesktopPublicKey())->toBeString()
        ->and($configManager->getDesktopMaxDrift())->toBeInt();
});

it('provides impersonation gate getter', function () {
    $configManager = ConfigManager::make();

    expect($configManager->getImpersonationGate())->toBeString();
});
