<?php

declare(strict_types=1);

namespace Akira\Spectra\Commands;

use Exception;
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

        $this->components->info('Building Spectra frontend assets...');

        if ($this->shouldBuildAssets()) {
            $this->buildAssets();
        } else {
            $this->components->info('Publishing pre-built Spectra assets...');

            $this->call('vendor:publish', [
                '--tag' => 'spectra-assets',
                '--force' => true,
            ]);
        }

        $this->components->info('Spectra installed successfully!');
        $this->components->info('Visit /spectra in your browser (when in local environment).');

        $this->newLine();
        $this->components->warn('Remember to configure your Gate for "use-spectra" permission.');
        $this->components->warn('Set SPECTRA_REQUIRE_AUTH=false in .env to disable authentication in development.');

        return self::SUCCESS;
    }

    private function shouldBuildAssets(): bool
    {
        $packagePath = base_path('vendor/akira/laravel-spectra');

        return is_dir($packagePath.'/resources/js') && is_file($packagePath.'/package.json');
    }

    private function buildAssets(): void
    {
        $packagePath = base_path('vendor/akira/laravel-spectra');

        $this->components->info('Installing npm dependencies...');
        $this->executeCommand("cd {$packagePath} && npm install", 'npm install');

        $this->components->info('Building frontend assets with Vite...');
        $this->executeCommand("cd {$packagePath} && npm run build", 'npm run build');

        $this->components->info('Publishing built assets to application...');
        $this->call('vendor:publish', [
            '--tag' => 'spectra-assets',
            '--force' => true,
        ]);
    }

    private function executeCommand(string $command, string $label): void
    {
        try {
            exec($command, $output, $exitCode);

            if ($exitCode !== 0) {
                $this->components->error("{$label} failed with exit code {$exitCode}");
                $this->components->error(implode("\n", $output));

                return;
            }

            $this->components->info("{$label} completed successfully.");
        } catch (Exception $e) {
            $this->components->error("Error running {$label}: ".$e->getMessage());
        }
    }
}
