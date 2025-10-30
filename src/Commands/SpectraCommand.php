<?php

namespace Akira\Spectra\Commands;

use Illuminate\Console\Command;

class SpectraCommand extends Command
{
    public $signature = 'laravel-spectra';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
