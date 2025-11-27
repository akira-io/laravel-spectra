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

        $manifest = $this->getManifest();

        return Inertia::render('Spectra', [
            'schemaUrl' => route('spectra.schema'),
            'executeUrl' => route('spectra.execute'),
            'cookiesUrl' => route('spectra.cookies'),
            'assets' => [
                'css' => asset('vendor/spectra/build/'.$manifest['resources/css/app.css']['file']),
                'js' => asset('vendor/spectra/build/'.$manifest['resources/js/spectra/main.tsx']['file']),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function getManifest(): array
    {
        $manifestPath = public_path('vendor/spectra/build/.vite/manifest.json');

        if (! file_exists($manifestPath)) {
            return [
                'resources/css/app.css' => ['file' => 'assets/app.css'],
                'resources/js/spectra/main.tsx' => ['file' => 'assets/main.js'],
            ];
        }

        return json_decode(file_get_contents($manifestPath), true);
    }
}
