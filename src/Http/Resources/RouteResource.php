<?php

declare(strict_types=1);

namespace Akira\Spectra\Http\Resources;

use Akira\Spectra\Dto\RouteMeta;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class RouteResource extends JsonResource
{
    public function __construct(private readonly RouteMeta $route)
    {
        parent::__construct($route);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uri' => $this->route->uri,
            'methods' => $this->route->methods,
            'name' => $this->route->name,
            'action' => $this->route->action,
            'middleware' => $this->route->middleware,
            'parameters' => array_map(
                fn ($param) => [
                    'name' => $param->name,
                    'required' => $param->required,
                    'where_pattern' => $param->wherePattern,
                ],
                $this->route->parameters
            ),
        ];
    }
}
