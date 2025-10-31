# Spectra API Inspector

**Illuminate your API** — Interactive API inspector for Laravel 12 with Inertia + React.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/akira/laravel-spectra.svg?style=flat-square)](https://packagist.org/packages/akira/laravel-spectra)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/akira-io/laravel-spectra/php-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/akira-io/laravel-spectra/actions?query=workflow%3Aphp-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/akira/laravel-spectra.svg?style=flat-square)](https://packagist.org/packages/akira/laravel-spectra)

Spectra is a powerful, developer-focused API inspector built exclusively for Laravel 12 applications. It provides an embedded, interactive console accessible at `/spectra` that helps you explore, test, and debug your API endpoints during development.

## Features

- 🔍 **Auto-discovery** of all application routes and parameters
- 📋 **JSON Schema generation** (2020-12) from FormRequest validation rules
- ⚡ **Internal request execution** through Laravel's HTTP kernel
- 🔐 **Multiple authentication modes**: current user, impersonate, Bearer token, Basic auth
- 🍪 **Cookie inspector** with Laravel encryption support
- 🎨 **Modern React UI** built with Inertia.js (no external packages needed)
- 💾 **Request collections** with export/import functionality
- 🌙 **Dark mode** support
- 🔒 **Production-safe** with comprehensive security controls

## Requirements

- PHP 8.4 or higher
- Laravel 12.x
- Inertia.js (automatically included)

## Installation

Install the package via Composer:

```bash
composer require --dev akira/laravel-spectra
```

Install and publish configuration:

```bash
php artisan spectra:install
```

This will publish the configuration file to `config/spectra.php`.

## Configuration

The configuration file provides comprehensive control over Spectra's behavior:

```php
return [
    // Enable/disable Spectra (defaults to local environment only)
    'enabled' => env('SPECTRA_ENABLED', app()->environment('local')),
    
    // Restrict to local environment only
    'only_local' => env('SPECTRA_ONLY_LOCAL', true),
    
    // Authentication guard to use
    'guard' => env('SPECTRA_GUARD', 'web'),
    
    // Gate for impersonation feature
    'impersonation_gate' => 'use-spectra',
    
    // Rate limiting for execute endpoint
    'rate_limit' => [
        'max' => 60,
        'per_minutes' => 1,
    ],
    
    // Headers to strip from requests
    'strip_headers' => [
        'authorization',
        'cookie',
        'x-api-key',
    ],
    
    // Fields to mask in responses
    'mask_fields' => [
        'password',
        'token',
        'authorization',
        'api_key',
        'secret',
    ],
];
```

## Usage

### Accessing Spectra

Once installed, visit `/spectra` in your browser when running in a local environment. You must be authenticated and have the `use-spectra` permission.

### Gate Configuration

By default, Spectra defines a `use-spectra` gate that checks if the user has a `developer` role. You can customize this in your `AuthServiceProvider`:

```php
use Illuminate\Support\Facades\Gate;

Gate::define('use-spectra', function ($user) {
    return $user->email === 'admin@example.com';
});
```

### Authentication Modes

Spectra supports four authentication modes for executing requests:

1. **Current User**: Execute requests as the currently authenticated user
2. **Impersonate**: Execute requests as a different user (requires `use-spectra` gate approval)
3. **Bearer Token**: Provide a Bearer token for authentication
4. **Basic Auth**: Use username/password authentication

### Working with Schemas

Spectra automatically generates JSON Schema (2020-12) from your FormRequest validation rules. Supported validation rules include:

- Basic types: `string`, `integer`, `numeric`, `boolean`, `array`
- Formats: `email`, `url`, `date`, `uuid`
- Constraints: `min`, `max`, `between`, `in` (enum), `regex`
- Files: `file`, `image`, `mimes`
- Modifiers: `nullable`, `required`, `sometimes`

### Request Collections

Save frequently used requests as collections:

1. Configure your request (endpoint, parameters, auth mode)
2. Click "Save" in the Collections panel
3. Give it a name
4. Load it anytime with one click

Export/import collections as JSON for sharing with your team.

## Security

Spectra is designed with security as a top priority:

- **Disabled by default** outside local environments
- **Rate limiting** on request execution
- **Sensitive header stripping** (Authorization, Cookie, etc.)
- **Field masking** for sensitive data in responses
- **Gate-based authorization** for all features
- **No external network requests** — all execution is internal

### Production Safety

Spectra will automatically return a 404 error when:
- `enabled` config is `false`
- `only_local` is `true` and the environment is not local

**Never enable Spectra in production environments.**

## Extensibility

### Service Container Bindings

All Spectra services are bound to the container and can be extended or replaced:

```php
app()->bind(RouteScanner::class, function ($app) {
    return new CustomRouteScanner($app['router']);
});
```

### Custom Schema Builders

Override the schema builder to add custom rule conversions:

```php
app()->extend(SchemaBuilder::class, function ($builder, $app) {
    // Add custom logic
    return $builder;
});
```

## Testing

Run the test suite:

```bash
composer test
```

Run static analysis:

```bash
composer analyse
```

Format code:

```bash
composer format
```

## CI/CD

Spectra includes GitHub Actions workflows for:

- PHP tests with Pest
- Static analysis with Larastan
- Code style with Pint
- JavaScript build and type checking
- Commitlint for conventional commits
- Automated releases with release-it

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Contributions are welcome! Please follow the conventional commits specification for all commits.

## Security Vulnerabilities

If you discover a security vulnerability, please email security@akira-io.com. All security vulnerabilities will be promptly addressed.

## Credits

- [Kidiatoliny](https://github.com/kidiatoliny)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

