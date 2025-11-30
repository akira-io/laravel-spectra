<?php

declare(strict_types=1);

namespace Akira\Spectra;

use Akira\Spectra\Commands\InstallCommand;
use Akira\Spectra\Commands\SyncDesktopCommand;
use Akira\Spectra\Support\ConfigManager;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class SpectraServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-spectra')
            ->hasConfigFile('spectra')
            ->hasConfigFile('spectra-desktop')
            ->hasCommand(InstallCommand::class)
            ->hasCommand(SyncDesktopCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(ConfigManager::class, fn () => ConfigManager::make());

        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/spectra'),
        ], 'spectra-assets');
    }

    public function packageBooted(): void
    {
        JsonResource::withoutWrapping();

        $configManager = $this->app->make(ConfigManager::class);

        if ($configManager->isEnabled()) {
            $this->loadRoutesFrom(__DIR__.'/../routes/spectra.php');
            $this->loadViewsFrom(__DIR__.'/../resources/views', 'spectra');
        }

        Gate::define('use-spectra', function ($user) {
            if (method_exists($user, 'hasRole')) {
                return $user->hasRole('developer');
            }

            return true;
        });
    }
}
