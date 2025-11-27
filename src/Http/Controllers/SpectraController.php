<?php

declare(strict_types=1);

namespace Akira\Spectra\Http\Controllers;

use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;

final class SpectraController extends Controller
{
    public function index(): Response
    {
        Inertia::setRootView('spectra::app');

        return Inertia::render('Spectra', [
            'schemaUrl' => route('spectra.schema'),
            'executeUrl' => route('spectra.execute'),
            'cookiesUrl' => route('spectra.cookies'),
        ]);
    }
}
