<?php

declare(strict_types=1);

namespace Akira\Spectra\Dto;

enum AuthMode: string
{
    case CURRENT = 'current';
    case IMPERSONATE = 'impersonate';
    case BEARER = 'bearer';
    case BASIC = 'basic';
}
