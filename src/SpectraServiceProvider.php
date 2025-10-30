<?php

namespace Akira\Spectra;

use Akira\Spectra\Commands\SpectraCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

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
