<?php

declare(strict_types=1);

namespace Akira\Spectra\Pipes;

use Akira\Spectra\Data\SpectraPayloadVO;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;
use ReflectionClass;
use Throwable;

final readonly class CollectModelsPipe
{
    public function __construct(private Filesystem $filesystem) {}

    /**
     * @param  Closure(SpectraPayloadVO): SpectraPayloadVO  $next
     */
    public function __invoke(SpectraPayloadVO $payload, Closure $next): SpectraPayloadVO
    {
        /** @var array<string, mixed> $models */
        $models = $this->discoverModels();

        return $next(new SpectraPayloadVO(
            routes: $payload->routes,
            models: $models,
            stats: $payload->stats,
            version: $payload->version,
            projectPath: $payload->projectPath,
            fingerprint: $payload->fingerprint,
        ));
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function discoverModels(): array
    {
        $modelsPath = app_path('Models');
        /** @var array<int, array<string, string>> $models */
        $models = [];

        if (! $this->filesystem->exists($modelsPath)) {
            return $models;
        }

        $files = $this->filesystem->files($modelsPath);

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $className = 'App\\Models\\'.$file->getBasename('.php');

            if (! class_exists($className)) {
                continue;
            }

            try {
                $reflection = new ReflectionClass($className);

                if ($reflection->isAbstract() || ! is_subclass_of($className, Model::class)) {
                    continue;
                }

                /** @var Model $instance */
                $instance = new $className();
                $models[] = [
                    'name' => $className,
                    'table' => $instance->getTable(),
                ];
            } catch (Throwable) {
                continue;
            }
        }

        return $models;
    }
}
