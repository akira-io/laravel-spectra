<?php

declare(strict_types=1);

namespace Akira\Spectra\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

final readonly class EnsureSpectraEnabled
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (! config('spectra.enabled')) {
            abort(404);
        }

        if (config('spectra.only_local') && ! app()->isLocal()) {
            abort(404);
        }

        return $next($request);
    }
}
