<?php

declare(strict_types=1);

use Akira\Spectra\Http\Controllers\CookieController;
use Akira\Spectra\Http\Controllers\ExecuteController;
use Akira\Spectra\Http\Controllers\SchemaController;
use Akira\Spectra\Http\Controllers\SpectraController;
use Akira\Spectra\Http\Controllers\SpectraDesktopController;
use Akira\Spectra\Http\Middleware\EnsureSpectraEnabled;
use Akira\Spectra\Http\Middleware\VerifyDesktopSignature;
use Illuminate\Support\Facades\Route;

$middleware = ['web', EnsureSpectraEnabled::class];

if (config('spectra.require_auth', true)) {
    $middleware[] = 'auth';
    $middleware[] = 'can:use-spectra';
}

Route::middleware($middleware)
    ->prefix('spectra')
    ->name('spectra.')
    ->group(function () {
        Route::get('/', [SpectraController::class, 'index'])->name('index');
        Route::get('/schema', [SchemaController::class, 'show'])->name('schema');
        Route::post('/execute', [ExecuteController::class, 'store'])->name('execute');
        Route::get('/cookies', [CookieController::class, 'index'])->name('cookies');
        Route::get('/system-metrics', [SpectraController::class, 'systemMetrics'])->name('system-metrics');
    });

Route::middleware([VerifyDesktopSignature::class])
    ->prefix('spectra/desktop')
    ->name('spectra.desktop.')
    ->group(function () {
        Route::get('/ping', [SpectraDesktopController::class, 'ping'])->name('ping');
        Route::get('/export', [SpectraDesktopController::class, 'export'])->name('export');
        Route::post('/force-sync', [SpectraDesktopController::class, 'forceSync'])->name('force-sync');
    });
