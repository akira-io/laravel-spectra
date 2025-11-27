# Configuration

Spectra provides comprehensive configuration options to control its behavior, security, and features.

## Configuration File

The configuration file is located at `config/spectra.php` after running `php artisan spectra:install`.

## Configuration Options

### Enable/Disable Spectra

```php
'enabled' => env('SPECTRA_ENABLED', app()->environment('local')),
```

Controls whether Spectra is enabled. By default, it's only enabled in local environments.

**Environment Variable:** `SPECTRA_ENABLED`  
**Default:** `true` in local environment, `false` otherwise  
**Type:** `boolean`

**Example:**
```env
SPECTRA_ENABLED=true
```

### Local-Only Mode

```php
'only_local' => env('SPECTRA_ONLY_LOCAL', true),
```

When `true`, Spectra will only work in local environments, regardless of the `enabled` setting.

**Environment Variable:** `SPECTRA_ONLY_LOCAL`  
**Default:** `true`  
**Type:** `boolean`

**Example:**
```env
SPECTRA_ONLY_LOCAL=true
```

> **Security Warning:** Never set this to `false` in production environments.

### Authentication Guard

```php
'guard' => env('SPECTRA_GUARD', 'web'),
```

The authentication guard to use for Spectra. This determines how users are authenticated when accessing Spectra.

**Environment Variable:** `SPECTRA_GUARD`  
**Default:** `web`  
**Type:** `string`

**Example:**
```env
SPECTRA_GUARD=web
```

### Impersonation Gate

```php
'impersonation_gate' => 'use-spectra',
```

The gate used to authorize impersonation features. Users must pass this gate to use the impersonate feature.

**Default:** `use-spectra`  
**Type:** `string`

**Example:**
```php
// In AuthServiceProvider
Gate::define('use-spectra', function ($user) {
    return $user->hasRole('developer') || $user->hasRole('admin');
});
```

### Rate Limiting

```php
'rate_limit' => [
    'max' => 60,
    'per_minutes' => 1,
],
```

Rate limiting for the execute endpoint to prevent abuse.

**Default:** 60 requests per minute  
**Type:** `array`

**Options:**
- `max`: Maximum number of requests
- `per_minutes`: Time window in minutes

**Example:**
```php
'rate_limit' => [
    'max' => 120,
    'per_minutes' => 1,
],
```

### Strip Headers

```php
'strip_headers' => [
    'authorization',
    'cookie',
    'x-api-key',
],
```

Headers to remove from requests before execution. These headers are stripped for security reasons.

**Default:** Common authentication headers  
**Type:** `array`

**Example:**
```php
'strip_headers' => [
    'authorization',
    'cookie',
    'x-api-key',
    'x-csrf-token',
    'x-custom-secret',
],
```

### Mask Fields

```php
'mask_fields' => [
    'password',
    'token',
    'authorization',
    'api_key',
    'secret',
],
```

Field names to mask in response bodies. Matching fields will show `***MASKED***` instead of actual values.

**Default:** Common sensitive field names  
**Type:** `array`

**Example:**
```php
'mask_fields' => [
    'password',
    'password_confirmation',
    'token',
    'access_token',
    'refresh_token',
    'authorization',
    'api_key',
    'secret',
    'private_key',
    'credit_card',
    'ssn',
],
```

## Environment-Specific Configuration

### Local Development

```env
SPECTRA_ENABLED=true
SPECTRA_ONLY_LOCAL=true
SPECTRA_GUARD=web
```

### Staging Environment

```env
SPECTRA_ENABLED=true
SPECTRA_ONLY_LOCAL=false
SPECTRA_GUARD=web
```

> **Caution:** Only enable on staging if absolutely necessary and with proper access controls.

### Production Environment

```env
SPECTRA_ENABLED=false
SPECTRA_ONLY_LOCAL=true
```

> **Security:** Never enable Spectra in production.

## Advanced Configuration

### Custom Route Prefix

By default, Spectra is accessible at `/spectra`. To change this, you can override the route registration:

```php
// In a service provider
public function boot(): void
{
    Route::group([
        'prefix' => 'api-inspector',
        'middleware' => ['web', 'auth'],
    ], function () {
        require base_path('vendor/akira/laravel-spectra/routes/spectra.php');
    });
}
```

### Custom Middleware

Add custom middleware to Spectra routes:

```php
// In config/spectra.php (if you extend the config)
'middleware' => [
    'web',
    'auth',
    \Akira\Spectra\Http\Middleware\EnsureSpectraEnabled::class,
    \App\Http\Middleware\CustomSpectraMiddleware::class,
],
```

### Custom Gate Logic

Implement complex authorization logic:

```php
Gate::define('use-spectra', function ($user) {
    // Check multiple conditions
    if ($user->hasRole('super-admin')) {
        return true;
    }
    
    if ($user->hasRole('developer') && app()->environment('local')) {
        return true;
    }
    
    // Check IP whitelist
    if (in_array(request()->ip(), config('spectra.allowed_ips', []))) {
        return true;
    }
    
    return false;
});
```

## Configuration Validation

Spectra validates its configuration on boot. If invalid configuration is detected, it will throw an exception.

### Common Validation Errors

1. **Invalid rate limit values**
   - `max` must be a positive integer
   - `per_minutes` must be a positive integer

2. **Invalid guard**
   - Guard must exist in `config/auth.php`

3. **Invalid arrays**
   - `strip_headers` and `mask_fields` must be arrays

## Security Best Practices

1. **Always use `only_local => true` in production**
2. **Restrict gate access to specific users or roles**
3. **Keep `strip_headers` comprehensive**
4. **Regularly review `mask_fields` for your application**
5. **Use environment variables for sensitive settings**
6. **Never commit enabled production settings to version control**

## Next Steps

- [Security](security.md) - Learn about security features
- [Authentication](authentication.md) - Configure authentication modes
- [Quick Start](quick-start.md) - Start using Spectra
