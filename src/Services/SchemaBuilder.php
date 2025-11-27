<?php

declare(strict_types=1);

namespace Akira\Spectra\Services;

use Akira\Spectra\Dto\RouteMeta;
use Akira\Spectra\Dto\SchemaSpec;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Routing\Router;
use Illuminate\Support\Str;
use ReflectionClass;

final readonly class SchemaBuilder
{
    public function __construct(
        private Router $router,
        private ValidationFactory $validation
    ) {}

    /**
     * @param  array<RouteMeta>  $routes
     * @return array<string, SchemaSpec>
     */
    public function buildSchemas(array $routes): array
    {
        $schemas = [];

        foreach ($routes as $route) {
            foreach ($route->methods as $method) {
                if (in_array($method, ['HEAD', 'OPTIONS'])) {
                    continue;
                }

                $key = $this->makeKey($route->name ?? $route->uri, $method);
                $schemas[$key] = $this->buildSchemaForRoute($route, $method);
            }
        }

        return $schemas;
    }

    private function buildSchemaForRoute(RouteMeta $route, string $method): SchemaSpec
    {
        $rules = $this->extractRules($route, $method);

        return new SchemaSpec(
            routeIdentifier: $route->name ?? $route->uri,
            method: $method,
            pathSchema: $this->buildPathSchema($route),
            querySchema: $this->buildQuerySchema($rules, $method),
            bodySchema: $this->buildBodySchema($rules, $method),
            headersSchema: $this->buildHeadersSchema(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function extractRules(RouteMeta $route, string $method): array
    {
        $laravelRoute = $this->router->getRoutes()->getByName($route->name ?? '');

        if (! $laravelRoute) {
            return [];
        }

        $action = $laravelRoute->getAction();

        if (! isset($action['uses']) || ! is_string($action['uses'])) {
            return [];
        }

        [$controller, $methodName] = Str::parseCallback($action['uses']);

        if (! $controller || ! class_exists($controller)) {
            return [];
        }

        try {
            $reflection = new ReflectionClass($controller);
            $methodReflection = $reflection->getMethod($methodName);

            foreach ($methodReflection->getParameters() as $parameter) {
                $type = $parameter->getType();

                if (! $type || $type->isBuiltin()) {
                    continue;
                }

                $className = $type->getName();

                if (class_exists($className) && method_exists($className, 'rules')) {
                    $formRequest = new $className;

                    return $formRequest->rules();
                }
            }
        } catch (\ReflectionException) {
            return [];
        }

        return [];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPathSchema(RouteMeta $route): array
    {
        if (empty($route->parameters)) {
            return [
                '$schema' => 'https://json-schema.org/draft/2020-12/schema',
                'type' => 'object',
                'properties' => [],
            ];
        }

        $properties = [];
        $required = [];

        foreach ($route->parameters as $param) {
            $properties[$param->name] = $this->inferTypeFromPattern($param->wherePattern);

            if ($param->required) {
                $required[] = $param->name;
            }
        }

        return [
            '$schema' => 'https://json-schema.org/draft/2020-12/schema',
            'type' => 'object',
            'properties' => $properties,
            'required' => $required,
        ];
    }

    /**
     * @param  array<string, mixed>  $rules
     * @return array<string, mixed>
     */
    private function buildQuerySchema(array $rules, string $method): array
    {
        if (! in_array($method, ['GET', 'DELETE'])) {
            return [
                '$schema' => 'https://json-schema.org/draft/2020-12/schema',
                'type' => 'object',
                'properties' => [],
            ];
        }

        return $this->rulesToSchema($rules);
    }

    /**
     * @param  array<string, mixed>  $rules
     * @return array<string, mixed>
     */
    private function buildBodySchema(array $rules, string $method): array
    {
        if (in_array($method, ['GET', 'DELETE', 'HEAD'])) {
            return [
                '$schema' => 'https://json-schema.org/draft/2020-12/schema',
                'type' => 'object',
                'properties' => [],
            ];
        }

        return $this->rulesToSchema($rules);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildHeadersSchema(): array
    {
        return [
            '$schema' => 'https://json-schema.org/draft/2020-12/schema',
            'type' => 'object',
            'properties' => [
                'Accept' => ['type' => 'string', 'default' => 'application/json'],
                'Content-Type' => ['type' => 'string', 'default' => 'application/json'],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $rules
     * @return array<string, mixed>
     */
    private function rulesToSchema(array $rules): array
    {
        $properties = [];
        $required = [];

        foreach ($rules as $field => $ruleset) {
            $ruleArray = is_string($ruleset) ? explode('|', $ruleset) : $ruleset;
            $schema = $this->convertRulesToJsonSchema($ruleArray);

            if (str_contains($field, '.')) {
                $this->addNestedProperty($properties, $field, $schema);
            } else {
                $properties[$field] = $schema;
            }

            if ($this->isRequired($ruleArray)) {
                $required[] = explode('.', $field)[0];
            }
        }

        return [
            '$schema' => 'https://json-schema.org/draft/2020-12/schema',
            'type' => 'object',
            'properties' => $properties,
            'required' => array_unique($required),
        ];
    }

    /**
     * @param  array<string, mixed>  $properties
     * @param  array<string, mixed>  $schema
     */
    private function addNestedProperty(array &$properties, string $field, array $schema): void
    {
        $parts = explode('.', $field);
        $current = &$properties;

        foreach ($parts as $index => $part) {
            if ($part === '*') {
                if (! isset($current['items'])) {
                    $current['type'] = 'array';
                    $current['items'] = ['type' => 'object', 'properties' => []];
                }
                $current = &$current['items']['properties'];
            } else {
                if ($index === count($parts) - 1) {
                    $current[$part] = $schema;
                } else {
                    if (! isset($current[$part])) {
                        $current[$part] = ['type' => 'object', 'properties' => []];
                    }
                    $current = &$current[$part]['properties'];
                }
            }
        }
    }

    /**
     * @param  array<mixed>  $rules
     * @return array<string, mixed>
     */
    private function convertRulesToJsonSchema(array $rules): array
    {
        $schema = ['type' => 'string'];
        $nullable = false;

        foreach ($rules as $rule) {
            if (is_object($rule)) {
                $rule = get_class($rule);
            }

            $ruleName = is_string($rule) ? explode(':', $rule)[0] : '';
            $ruleParams = is_string($rule) && str_contains($rule, ':') ? explode(',', explode(':', $rule)[1]) : [];

            match ($ruleName) {
                'required' => $schema['required'] = true,
                'nullable' => $nullable = true,
                'string' => $schema['type'] = 'string',
                'integer', 'int' => $schema['type'] = 'integer',
                'numeric', 'number' => $schema['type'] = 'number',
                'boolean', 'bool' => $schema['type'] = 'boolean',
                'array' => $schema['type'] = 'array',
                'email' => $schema['format'] = 'email',
                'url' => $schema['format'] = 'uri',
                'date' => $schema['format'] = 'date',
                'date_format' => $schema['format'] = 'date-time',
                'uuid' => $schema['format'] = 'uuid',
                'min' => $schema['minLength'] = (int) ($ruleParams[0] ?? 0),
                'max' => $schema['maxLength'] = (int) ($ruleParams[0] ?? 255),
                'between' => [
                    $schema['minLength'] = (int) ($ruleParams[0] ?? 0),
                    $schema['maxLength'] = (int) ($ruleParams[1] ?? 255),
                ],
                'in' => $schema['enum'] = $ruleParams,
                'regex' => $schema['pattern'] = $ruleParams[0] ?? '',
                'file', 'image' => [
                    $schema['type'] = 'string',
                    $schema['format'] = 'binary',
                ],
                'mimes' => $schema['contentMediaType'] = implode('|', array_map(fn ($m) => ".$m", $ruleParams)),
                default => null,
            };
        }

        if ($nullable) {
            $schema['type'] = [$schema['type'], 'null'];
        }

        return $schema;
    }

    /**
     * @param  array<mixed>  $rules
     */
    private function isRequired(array $rules): bool
    {
        foreach ($rules as $rule) {
            if ($rule === 'required' || (is_string($rule) && str_starts_with($rule, 'required'))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    private function inferTypeFromPattern(?string $pattern): array
    {
        if (! $pattern) {
            return ['type' => 'string'];
        }

        if ($pattern === '[0-9]+') {
            return ['type' => 'integer'];
        }

        return ['type' => 'string', 'pattern' => $pattern];
    }

    private function makeKey(string $identifier, string $method): string
    {
        return sprintf('%s::%s', $identifier, strtoupper($method));
    }
}
