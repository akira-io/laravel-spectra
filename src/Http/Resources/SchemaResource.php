<?php

declare(strict_types=1);

namespace Akira\Spectra\Http\Resources;

use Akira\Spectra\Dto\SchemaSpec;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class SchemaResource extends JsonResource
{
    public function __construct(private readonly SchemaSpec $schema)
    {
        parent::__construct($schema);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'route' => $this->schema->routeIdentifier,
            'method' => $this->schema->method,
            'schemas' => [
                'path' => $this->schema->pathSchema,
                'query' => $this->schema->querySchema,
                'body' => $this->schema->bodySchema,
                'headers' => $this->schema->headersSchema,
            ],
        ];
    }
}
