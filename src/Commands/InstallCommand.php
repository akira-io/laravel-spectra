<?php

declare(strict_types=1);

namespace Akira\Spectra\Commands;

use Illuminate\Console\Command;

final class InstallCommand extends Command
{
    protected $signature = 'spectra:install';

    protected $description = 'Install Spectra API Inspector';

    public function handle(): int
    {
        $this->components->info('Publishing Spectra configuration...');

        $this->call('vendor:publish', [
            '--tag' => 'spectra-config',
            '--force' => true,
        ]);

        $this->components->info('Publishing Spectra assets...');

        $this->call('vendor:publish', [
            '--tag' => 'spectra-assets',
            '--force' => true,
        ]);

        $this->components->info('Spectra installed successfully!');
        $this->components->info('Visit /spectra in your browser (when in local environment).');

        $this->newLine();
        $this->components->warn('Remember to configure your Gate for "use-spectra" permission.');
        $this->components->warn('Set SPECTRA_REQUIRE_AUTH=false in .env to disable authentication in development.');

        return self::SUCCESS;
    }
}
