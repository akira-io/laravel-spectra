<?php

declare(strict_types=1);

namespace Akira\Spectra\Support;

use Illuminate\Filesystem\Filesystem;

final readonly class SpectraRouteFingerprintStore
{
    private string $path;

    public function __construct(private Filesystem $filesystem)
    {
        $this->path = storage_path('framework/spectra/routes.hash');
    }

    public function get(): ?string
    {
        if (! $this->filesystem->exists($this->path)) {
            return null;
        }

        $content = $this->filesystem->get($this->path);

        return $content ?: null;
    }

    public function put(string $hash): void
    {
        $directory = dirname($this->path);

        if (! $this->filesystem->exists($directory)) {
            $this->filesystem->makeDirectory($directory, 0755, true);
        }

        $this->filesystem->put($this->path, $hash);
    }

    public function changed(string $new): bool
    {
        $current = $this->get();

        return $current !== $new;
    }
}
