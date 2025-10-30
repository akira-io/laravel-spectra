<?php

declare(strict_types=1);

namespace Akira\Spectra\Services;

use Akira\Spectra\Dto\AuthMode;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Gate;

final readonly class AuthBroker
{
    public function __construct(private AuthManager $auth) {}

    public function authenticate(
        AuthMode $mode,
        ?int $impersonateId = null,
        ?string $bearerToken = null,
        ?string $basicUser = null,
        ?string $basicPass = null
    ): ?Authenticatable {
        $guard = $this->getGuard();

        return match ($mode) {
            AuthMode::CURRENT => $guard->user(),
            AuthMode::IMPERSONATE => $this->impersonate($impersonateId),
            AuthMode::BEARER => $this->authenticateBearer($bearerToken),
            AuthMode::BASIC => $this->authenticateBasic($basicUser, $basicPass),
        };
    }

    private function getGuard(): Guard
    {
        $guardName = config('spectra.guard', 'web');

        return $this->auth->guard($guardName);
    }

    private function impersonate(?int $userId): ?Authenticatable
    {
        if (! $userId) {
            return null;
        }

        $currentUser = $this->getGuard()->user();

        if (! $currentUser || ! Gate::allows(config('spectra.impersonation_gate', 'use-spectra'), $currentUser)) {
            return null;
        }

        $userModel = $this->getGuard()->getProvider()?->getModel();

        if (! $userModel || ! class_exists($userModel)) {
            return null;
        }

        return $userModel::find($userId);
    }

    private function authenticateBearer(?string $token): ?Authenticatable
    {
        if (! $token) {
            return null;
        }

        $guard = $this->getGuard();

        if (method_exists($guard, 'setToken')) {
            $guard->setToken($token);

            return $guard->user();
        }

        return null;
    }

    private function authenticateBasic(?string $user, ?string $pass): ?Authenticatable
    {
        if (! $user || ! $pass) {
            return null;
        }

        $guard = $this->getGuard();

        if ($guard->attempt(['email' => $user, 'password' => $pass])) {
            return $guard->user();
        }

        return null;
    }
}
