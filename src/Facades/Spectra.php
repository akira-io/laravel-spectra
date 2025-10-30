<?php

namespace Akira\Spectra\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Akira\Spectra\Spectra
 */
class Spectra extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Akira\Spectra\Spectra::class;
    }
}
