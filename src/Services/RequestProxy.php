<?php

declare(strict_types=1);

namespace Akira\Spectra\Services;

use Akira\Spectra\Dto\ExecuteCommand;
use Akira\Spectra\Dto\ExecuteResult;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\RateLimiter;

final readonly class RequestProxy
{
    public function __construct(
        private Application $app,
        private AuthBroker $authBroker
    ) {}

    public function handle(ExecuteCommand $command): ExecuteResult
    {
        $this->checkRateLimit();

        $start = microtime(true);

        $user = $this->authBroker->authenticate(
            $command->authMode,
            $command->impersonateId,
            $command->bearerToken,
            $command->basicUser,
            $command->basicPass
        );

        if ($user) {
            auth()->setUser($user);
        }

        $uri = $this->buildUri($command);
        $headers = $this->sanitizeHeaders($command->headers);

        $request = Request::create(
            $uri,
            $command->method,
            $command->method === 'GET' ? $command->query : [],
            server: $this->buildServerArray($headers)
        );

        if (! in_array($command->method, ['GET', 'HEAD'])) {
            if (is_array($command->body)) {
                $request->merge($command->body);
            } else {
                $request->setContent($command->body);
            }
        }

        $response = $this->app->handle($request);

        $duration = (int) ((microtime(true) - $start) * 1000);
        $content = $response->getContent();
        $size = strlen($content ?: '');

        return new ExecuteResult(
            status: $response->getStatusCode(),
            timeMs: $duration,
            sizeBytes: $size,
            headers: $this->extractHeaders($response),
            body: $this->maskSensitiveFields($this->parseBody($content)),
        );
    }

    private function checkRateLimit(): void
    {
        $key = 'spectra:execute:'.request()->ip();
        $maxAttempts = config('spectra.rate_limit.max', 60);
        $decayMinutes = config('spectra.rate_limit.per_minutes', 1);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            abort(429, 'Too many requests');
        }

        RateLimiter::hit($key, $decayMinutes * 60);
    }

    private function buildUri(ExecuteCommand $command): string
    {
        $uri = $command->endpoint;

        foreach ($command->pathParams as $key => $value) {
            $uri = str_replace('{'.$key.'}', (string) $value, $uri);
        }

        if (! empty($command->query)) {
            $uri .= '?'.http_build_query($command->query);
        }

        return $uri;
    }

    /**
     * @param  array<string, string>  $headers
     * @return array<string, string>
     */
    private function sanitizeHeaders(array $headers): array
    {
        $stripHeaders = array_map('strtolower', config('spectra.strip_headers', []));

        return array_filter(
            $headers,
            fn ($key) => ! in_array(strtolower($key), $stripHeaders),
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * @param  array<string, string>  $headers
     * @return array<string, string>
     */
    private function buildServerArray(array $headers): array
    {
        $server = [];

        foreach ($headers as $key => $value) {
            $key = strtoupper(str_replace('-', '_', $key));
            $server['HTTP_'.$key] = $value;
        }

        return $server;
    }

    /**
     * @return array<string, string|array<string>>
     */
    private function extractHeaders($response): array
    {
        $headers = [];

        foreach ($response->headers->all() as $key => $values) {
            $headers[$key] = count($values) === 1 ? $values[0] : $values;
        }

        return $headers;
    }

    private function parseBody(?string $content): mixed
    {
        if (! $content) {
            return null;
        }

        $decoded = json_decode($content, true);

        return $decoded ?? $content;
    }

    private function maskSensitiveFields(mixed $data): mixed
    {
        if (! is_array($data)) {
            return $data;
        }

        $maskFields = array_map('strtolower', config('spectra.mask_fields', []));

        return $this->recursiveMask($data, $maskFields);
    }

    /**
     * @param  array<mixed>  $data
     * @param  array<string>  $maskFields
     * @return array<mixed>
     */
    private function recursiveMask(array $data, array $maskFields): array
    {
        foreach ($data as $key => $value) {
            if (in_array(strtolower((string) $key), $maskFields)) {
                $data[$key] = '***MASKED***';
            } elseif (is_array($value)) {
                $data[$key] = $this->recursiveMask($value, $maskFields);
            }
        }

        return $data;
    }
}
