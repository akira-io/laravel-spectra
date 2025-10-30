<?php

declare(strict_types=1);

namespace Akira\Spectra\Http\Controllers;

use Akira\Spectra\Actions\ExecuteRequestAction;
use Akira\Spectra\Dto\ExecuteCommand;
use Akira\Spectra\Http\Requests\ExecuteRequest;
use Akira\Spectra\Http\Resources\ExecuteResultResource;
use Illuminate\Routing\Controller;

final class ExecuteController extends Controller
{
    public function __construct(private readonly ExecuteRequestAction $executeRequest) {}

    public function store(ExecuteRequest $request): ExecuteResultResource
    {
        $command = ExecuteCommand::fromArray($request->validated());

        $result = $this->executeRequest->handle($command);

        return new ExecuteResultResource($result);
    }
}
