# Installation

This guide will walk you through installing Laravel Spectra in your Laravel 12 application.

## Requirements

Before installing Spectra, ensure your system meets the following requirements:

- **PHP**: 8.4 or higher
- **Laravel**: 12.x
- **Inertia.js**: 2.0 or higher (automatically included)
- **Composer**: Latest version recommended

## Installation Steps

### 1. Install via Composer

Install the package as a development dependency:

```bash
composer require --dev akira/laravel-spectra
```

> **Note:** Spectra is a development tool and should only be installed as a dev dependency. Never use it in production.

### 2. Run the Install Command

Publish the configuration file and assets:

```bash
php artisan spectra:install
```

This command will:
- Publish the configuration file to `config/spectra.php`
- Register the service provider (auto-discovered)
- Set up the necessary routes

### 3. Configure Environment

Add the following to your `.env` file:

```env
SPECTRA_ENABLED=true
SPECTRA_ONLY_LOCAL=true
SPECTRA_GUARD=web
```

### 4. Set Up Authorization

By default, Spectra uses a `use-spectra` gate. Define this gate in your `AuthServiceProvider`:

```php
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    Gate::define('use-spectra', function ($user) {
        // Allow developers or admins
        return in_array($user->email, [
            'admin@example.com',
            'developer@example.com',
        ]);
    });
}
```

Or check for a specific role:

```php
Gate::define('use-spectra', function ($user) {
    return $user->hasRole('developer');
});
```

### 5. Verify Installation

Visit `/spectra` in your browser while authenticated. You should see the Spectra interface.

## Post-Installation

### Building Assets (Optional)

If you need to customize the frontend, you can build the assets:

```bash
npm install
npm run build
```

### Customizing Configuration

Review and customize `config/spectra.php` according to your needs. See [Configuration](configuration.md) for details.

## Uninstallation

To remove Spectra from your application:

1. Remove the package:
```bash
composer remove akira/laravel-spectra
```

2. Delete the configuration file:
```bash
rm config/spectra.php
```

3. Clear your cache:
```bash
php artisan config:clear
php artisan route:clear
```

## Next Steps

- [Configuration](configuration.md) - Configure Spectra for your needs
- [Quick Start](quick-start.md) - Learn the basics
- [Security](security.md) - Understand security features
