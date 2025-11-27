<?php

declare(strict_types=1);

namespace Akira\Spectra\Services;

use Illuminate\Contracts\Encryption\Encrypter;

final readonly class CookieInspector
{
    public function __construct(private Encrypter $encrypter) {}

    /**
     * @return array<array<string, mixed>>
     */
    public function inspect(): array
    {
        $cookies = [];

        foreach ($_COOKIE as $name => $value) {
            $cookies[] = [
                'name' => $name,
                'value' => $this->decryptValue($value),
                'encrypted' => $this->isEncrypted($value),
                'raw' => $value,
            ];
        }

        foreach (request()->cookies->all() as $name => $value) {
            if (! isset($_COOKIE[$name])) {
                $cookies[] = [
                    'name' => $name,
                    'value' => $this->decryptValue($value),
                    'encrypted' => $this->isEncrypted($value),
                    'raw' => $value,
                ];
            }
        }

        return $cookies;
    }

    private function decryptValue(string $value): string
    {
        try {
            return $this->encrypter->decrypt($value, false);
        } catch (\Exception) {
            return $value;
        }
    }

    private function isEncrypted(string $value): bool
    {
        try {
            $this->encrypter->decrypt($value, false);

            return true;
        } catch (\Exception) {
            return false;
        }
    }
}
