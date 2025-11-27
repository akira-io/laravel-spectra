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
            'assets' => $this->getAssetPaths(),
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function getAssetPaths(): array
    {
        $manifestPath = public_path('vendor/spectra/build/.vite/manifest.json');

        if (!file_exists($manifestPath)) {
            return [
                'css' => asset('vendor/spectra/build/assets/app.css'),
                'js' => asset('vendor/spectra/build/assets/main.js'),
            ];
        }

        $manifest = json_decode(file_get_contents($manifestPath), true) ?? [];

        $cssFile = $manifest['resources/css/app.css']['file'] ?? 'assets/app.css';
        $jsFile = $manifest['resources/js/spectra/main.tsx']['file'] ?? 'assets/main.js';

        return [
            'css' => asset('vendor/spectra/build/'.$cssFile),
            'js' => asset('vendor/spectra/build/'.$jsFile),
        ];
    }
}
