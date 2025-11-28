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
            'systemMetricsUrl' => route('spectra.system-metrics'),
            'assets' => $this->getAssetPaths(),
        ]);
    }

    public function systemMetrics(): \Illuminate\Http\JsonResponse
    {
        $memoryLimit = ini_get('memory_limit');
        $memoryUsage = memory_get_usage(true);
        $memoryLimitBytes = $this->convertToBytes($memoryLimit);
        
        return response()->json([
            'memory' => [
                'used' => $this->formatBytes($memoryUsage),
                'limit' => $memoryLimit,
                'percentage' => $memoryLimitBytes > 0 ? round(($memoryUsage / $memoryLimitBytes) * 100, 1) : 0,
            ],
            'debug' => config('app.debug'),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
            'db_connection' => config('database.default'),
        ]);
    }

    private function convertToBytes(string $value): int
    {
        $value = trim($value);
        $unit = strtolower($value[strlen($value) - 1]);
        $number = (int) $value;

        return match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => $number,
        };
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1024 * 1024 * 1024) {
            return round($bytes / (1024 * 1024 * 1024), 2) . 'GB';
        }
        if ($bytes >= 1024 * 1024) {
            return round($bytes / (1024 * 1024), 2) . 'MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 2) . 'KB';
        }
        return $bytes . 'B';
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
