<?php

declare(strict_types=1);

use Akira\Spectra\Http\Controllers\CookieController;
use Akira\Spectra\Http\Controllers\ExecuteController;
use Akira\Spectra\Http\Controllers\SchemaController;
use Akira\Spectra\Http\Controllers\SpectraController;
use Akira\Spectra\Http\Middleware\EnsureSpectraEnabled;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'can:use-spectra', EnsureSpectraEnabled::class])
    ->prefix('spectra')
    ->name('spectra.')
    ->group(function () {
        Route::get('/', [SpectraController::class, 'index'])->name('index');
        Route::get('/schema', [SchemaController::class, 'show'])->name('schema');
        Route::post('/execute', [ExecuteController::class, 'store'])->name('execute');
        Route::get('/cookies', [CookieController::class, 'index'])->name('cookies');
    });
