<?php

declare(strict_types=1);

namespace Akira\Spectra;

use Akira\Spectra\Commands\InstallCommand;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class SpectraServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-spectra')
            ->hasConfigFile('spectra')
            ->hasCommand(InstallCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/spectra'),
        ], 'spectra-assets');
    }

    public function packageBooted(): void
    {
        // Remove data wrapping from JSON resources
        JsonResource::withoutWrapping();

        if (config('spectra.enabled')) {
            $this->loadRoutesFrom(__DIR__.'/../routes/spectra.php');
            $this->loadViewsFrom(__DIR__.'/../resources/views', 'spectra');

            // Register Vite assets for package
            Vite::useBuildPath('vendor/spectra/build');
            Vite::useHotFile(storage_path('logs/spectra-vite.hot'));
        }

        Gate::define('use-spectra', function ($user) {
            if (method_exists($user, 'hasRole')) {
                return $user->hasRole('developer');
            }

            return true;
        });
    }
}
