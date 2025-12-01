<?php

declare(strict_types=1);

namespace Akira\Spectra\Tests;

use Akira\Spectra\SpectraServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Akira\\Spectra\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    final public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        config()->set('spectra.enabled', true);
        config()->set('spectra.require_auth', false);
        config()->set('spectra-desktop.enabled', false);
        config()->set('spectra-desktop.public_key', 'test-public-key');

        /*
         foreach (\Illuminate\Support\Facades\File::allFiles(__DIR__ . '/../database/migrations') as $migration) {
            (include $migration->getRealPath())->up();
         }
         */
    }

    protected function getPackageProviders($app)
    {
        return [
            SpectraServiceProvider::class,
        ];
    }
}
