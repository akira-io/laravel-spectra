<?php

namespace Akira\Spectra;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Akira\Spectra\Commands\SpectraCommand;

class SpectraServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-spectra')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_spectra_table')
            ->hasCommand(SpectraCommand::class);
    }
}
