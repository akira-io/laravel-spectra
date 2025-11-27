<?php

declare(strict_types=1);

namespace Akira\Spectra\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

final readonly class EnsureSpectraEnabled
{
    public function handle(Request $request, Closure $next): mixed
    {
        abort_unless(config('spectra.enabled'), 404);

        abort_if(config('spectra.only_local') && ! app()->isLocal(), 404);

        return $next($request);
    }
}
