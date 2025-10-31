<?php

declare(strict_types=1);

namespace Akira\Spectra;

use Akira\Spectra\Commands\InstallCommand;
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
            ->hasCommand(InstallCommand::class);
    }

    public function packageBooted(): void
    {
        if (config('spectra.enabled')) {
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
