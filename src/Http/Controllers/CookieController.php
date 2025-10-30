<?php

declare(strict_types=1);

namespace Akira\Spectra\Http\Controllers;

use Akira\Spectra\Actions\ListCookiesAction;
use Akira\Spectra\Http\Resources\CookieResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

final class CookieController extends Controller
{
    public function __construct(private readonly ListCookiesAction $listCookies) {}

    public function index(): AnonymousResourceCollection
    {
        $cookies = $this->listCookies->handle();

        return CookieResource::collection($cookies);
    }
}
