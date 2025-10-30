<?php

declare(strict_types=1);

namespace Akira\Spectra\Actions;

use Akira\Spectra\Dto\ExecuteCommand;
use Akira\Spectra\Dto\ExecuteResult;
use Akira\Spectra\Services\RequestProxy;

final readonly class ExecuteRequestAction
{
    public function __construct(private RequestProxy $proxy) {}

    public function handle(ExecuteCommand $command): ExecuteResult
    {
        return $this->proxy->handle($command);
    }
}
