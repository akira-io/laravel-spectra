<?php

declare(strict_types=1);

use Akira\Spectra\Dto\AuthMode;
use Akira\Spectra\Dto\ExecuteCommand;
use Akira\Spectra\Services\RequestProxy;

it('creates execute command from array', function () {
    $data = [
        'endpoint' => '/test',
        'method' => 'GET',
        'auth_mode' => 'current',
    ];

    $command = ExecuteCommand::fromArray($data);

    expect($command->endpoint)->toBe('/test')
        ->and($command->method)->toBe('GET')
        ->and($command->authMode)->toBe(AuthMode::CURRENT);
});

it('handles auth mode enum correctly', function () {
    $modes = ['current', 'impersonate', 'bearer', 'basic'];

    foreach ($modes as $mode) {
        $authMode = AuthMode::from($mode);
        expect($authMode)->toBeInstanceOf(AuthMode::class);
    }
});

it('validates execute command with impersonate mode', function () {
    $data = [
        'endpoint' => '/test',
        'method' => 'POST',
        'auth_mode' => 'impersonate',
        'impersonate_id' => 1,
    ];

    $command = ExecuteCommand::fromArray($data);

    expect($command->authMode)->toBe(AuthMode::IMPERSONATE)
        ->and($command->impersonateId)->toBe(1);
});
