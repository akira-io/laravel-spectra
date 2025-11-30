<?php

declare(strict_types=1);

namespace Akira\Spectra\Commands;

use Akira\Spectra\Actions\SendSpectraPayloadToDesktopAction;
use Akira\Spectra\Pipelines\BuildSpectraPayloadPipeline;
use Illuminate\Console\Command;

final class SyncDesktopCommand extends Command
{
    protected $signature = 'spectra:sync-desktop';

    protected $description = 'Sync project data with Spectra Desktop App';

    public function __construct(
        private BuildSpectraPayloadPipeline $payloadPipeline,
        private SendSpectraPayloadToDesktopAction $sendAction,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Building Spectra payload...');

        $payload = $this->payloadPipeline->handle();

        $this->info("Payload built with {$payload->fingerprint}");

        $this->info('Sending payload to Spectra Desktop...');

        $this->sendAction->handle($payload);

        $this->info('Spectra Desktop sync completed successfully!');

        return self::SUCCESS;
    }
}
