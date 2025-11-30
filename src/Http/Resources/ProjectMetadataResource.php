<?php

declare(strict_types=1);

namespace Akira\Spectra\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $projectName
 * @property string $phpVersion
 * @property string $laravelVersion
 * @property string $spectraVersion
 * @property string|null $lastFingerprint
 */
final class ProjectMetadataResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'project_name' => config('app.name'),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'spectra_version' => '1.0.0',
            'last_fingerprint' => $this->lastFingerprint,
        ];
    }
}
