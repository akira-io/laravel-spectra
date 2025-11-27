# Security

Security is a top priority for Spectra. This document outlines the security features, best practices, and considerations.

## Security Philosophy

Spectra follows a **defense-in-depth** approach with multiple layers of security:

1. Environment-based enablement
2. Gate-based authorization
3. Rate limiting
4. Sensitive data protection
5. Internal execution only
6. Production safety checks

## Default Security Posture

### Disabled by Default

Spectra is **disabled** outside of local environments:

```php
'enabled' => env('SPECTRA_ENABLED', app()->environment('local')),
'only_local' => env('SPECTRA_ONLY_LOCAL', true),
```

This ensures Spectra is never accidentally exposed in production.

### Development Dependency

Spectra should only be installed via:

```bash
composer require --dev akira/laravel-spectra
```

This prevents it from being deployed to production when using `composer install --no-dev`.

## Security Features

### 1. Environment Restrictions

**Local-Only Mode**

When `only_local` is `true`, Spectra will only work in local environments:

```php
if (config('spectra.only_local') && !app()->environment('local')) {
    abort(404);
}
```

**Explicit Enablement**

Spectra must be explicitly enabled via configuration:

```php
if (!config('spectra.enabled')) {
    abort(404);
}
```

### 2. Gate-Based Authorization

**Default Gate**

The `use-spectra` gate controls access:

```php
Gate::define('use-spectra', function ($user) {
    return $user->hasRole('developer');
});
```

**Gate Checks**

Every Spectra route is protected:

```php
Route::middleware(['auth', EnsureSpectraEnabled::class])
    ->group(function () {
        // Spectra routes
    });
```

**Impersonation Gate**

Impersonation requires additional authorization:

```php
if (!Gate::allows(config('spectra.impersonation_gate'))) {
    throw new AuthorizationException();
}
```

### 3. Rate Limiting

**Execute Endpoint Rate Limiting**

The execute endpoint is rate-limited to prevent abuse:

```php
'rate_limit' => [
    'max' => 60,           // Maximum requests
    'per_minutes' => 1,    // Time window
],
```

**Implementation**

```php
Route::post('/execute', ExecuteController::class)
    ->middleware('throttle:spectra-execute');
```

**Custom Rate Limits**

Configure per environment:

```php
// Local: More permissive
'rate_limit' => ['max' => 120, 'per_minutes' => 1],

// Staging: More restrictive
'rate_limit' => ['max' => 30, 'per_minutes' => 1],
```

### 4. Sensitive Data Protection

**Header Stripping**

Sensitive headers are removed before execution:

```php
'strip_headers' => [
    'authorization',
    'cookie',
    'x-api-key',
    'x-csrf-token',
],
```

**Why?** Prevents accidental exposure of credentials in logs or error messages.

**Field Masking**

Sensitive fields are masked in responses:

```php
'mask_fields' => [
    'password',
    'token',
    'authorization',
    'api_key',
    'secret',
],
```

**Masked Output**:
```json
{
  "password": "***MASKED***",
  "api_key": "***MASKED***",
  "name": "John Doe"
}
```

**Recursive Masking**

Masking works recursively in nested objects and arrays:

```json
{
  "user": {
    "password": "***MASKED***",
    "profile": {
      "secret": "***MASKED***"
    }
  }
}
```

### 5. Internal Execution

**No External HTTP Calls**

Requests are executed internally through Laravel's kernel:

```php
$response = $this->kernel->handle(
    Request::create($command->uri, $command->method)
);
```

**Benefits**:
- No DNS resolution
- No network latency
- No external data leakage
- Respects application state

### 6. Authentication Isolation

**Separate Auth Context**

Each execution creates an isolated authentication context:

```php
Auth::guard($guard)->setUser($user);
```

**No Session Persistence**

Authentication changes don't persist to the user's session:

```php
// After execution
Auth::guard($guard)->setUser($originalUser);
```

## Threat Model

### Threats Mitigated

#### 1. Unauthorized Access
**Threat**: Unauthenticated users accessing Spectra  
**Mitigation**: `auth` middleware + `use-spectra` gate

#### 2. Production Exposure
**Threat**: Spectra enabled in production  
**Mitigation**: `only_local` config + environment checks

#### 3. Credential Leakage
**Threat**: Credentials exposed in responses  
**Mitigation**: Header stripping + field masking

#### 4. Privilege Escalation
**Threat**: Users impersonating admins  
**Mitigation**: Impersonation gate + authorization checks

#### 5. Rate Limit Abuse
**Threat**: Excessive requests overloading server  
**Mitigation**: Rate limiting + throttling

#### 6. CSRF Attacks
**Threat**: Cross-site request forgery  
**Mitigation**: Laravel's CSRF protection

### Threats NOT Mitigated

#### 1. Malicious Insiders
If a user has `use-spectra` permission, they can execute arbitrary API requests. **Mitigation**: Strict gate controls.

#### 2. Server-Side Request Forgery (SSRF)
Internal execution could target local services. **Mitigation**: Validate URIs, restrict to app routes.

#### 3. Data Exfiltration
Authorized users can read API responses. **Mitigation**: Limit gate access, audit logs.

## Best Practices

### 1. Restrict Gate Access

**Recommended**: Whitelist specific users

```php
Gate::define('use-spectra', function ($user) {
    return in_array($user->email, [
        'dev@example.com',
        'admin@example.com',
    ]);
});
```

**Not Recommended**: Broad role checks

```php
// Too permissive
Gate::define('use-spectra', function ($user) {
    return $user->hasRole('employee');
});
```

### 2. Environment Configuration

**Local Development**
```env
SPECTRA_ENABLED=true
SPECTRA_ONLY_LOCAL=true
```

**Staging** (if needed)
```env
SPECTRA_ENABLED=true
SPECTRA_ONLY_LOCAL=false
# Ensure strict gate controls!
```

**Production**
```env
SPECTRA_ENABLED=false
SPECTRA_ONLY_LOCAL=true
# Or simply don't set these - defaults are safe
```

### 3. Sensitive Field Configuration

**Review and Update**

Regularly review `mask_fields` for your application:

```php
'mask_fields' => [
    // Authentication
    'password',
    'password_confirmation',
    'token',
    'access_token',
    'refresh_token',
    
    // API Keys
    'api_key',
    'secret_key',
    'private_key',
    
    // Sensitive Data
    'ssn',
    'credit_card',
    'cvv',
    
    // Application-Specific
    'encryption_key',
    'webhook_secret',
],
```

### 4. Header Stripping

**Review and Update**

Add application-specific sensitive headers:

```php
'strip_headers' => [
    'authorization',
    'cookie',
    'x-api-key',
    'x-csrf-token',
    
    // Application-Specific
    'x-internal-auth',
    'x-service-token',
    'x-custom-secret',
],
```

### 5. Audit Logging

**Log Spectra Usage**

Consider logging all Spectra requests:

```php
// In ExecuteController
Log::channel('spectra')->info('Request executed', [
    'user' => auth()->id(),
    'method' => $command->method,
    'uri' => $command->uri,
    'auth_mode' => $command->authMode->value,
]);
```

### 6. Network Isolation

**Docker/Container Environments**

Restrict Spectra container access:

```yaml
services:
  app:
    ports:
      - "8000:80"  # Only expose app port
    networks:
      - internal   # Isolate from external network
```

### 7. Dependency Management

**Keep Dependencies Updated**

```bash
composer update akira/laravel-spectra
```

**Monitor Security Advisories**

```bash
composer audit
```

## Security Checklist

Before deploying, verify:

- [ ] Spectra is installed as `--dev` dependency
- [ ] `SPECTRA_ENABLED=false` in production
- [ ] `SPECTRA_ONLY_LOCAL=true` in all environments
- [ ] `use-spectra` gate restricts access appropriately
- [ ] `mask_fields` includes all sensitive fields
- [ ] `strip_headers` includes all sensitive headers
- [ ] Rate limiting is configured appropriately
- [ ] Audit logging is enabled (if required)
- [ ] Composer `--no-dev` is used in production builds
- [ ] Environment variables are not committed to version control

## Incident Response

### Suspected Unauthorized Access

1. **Immediately disable Spectra**:
   ```env
   SPECTRA_ENABLED=false
   ```

2. **Clear application cache**:
   ```bash
   php artisan config:clear
   php artisan route:clear
   php artisan cache:clear
   ```

3. **Review logs** for suspicious activity:
   ```bash
   grep "spectra" storage/logs/*.log
   ```

4. **Revoke API tokens** if compromised

5. **Update gate** to restrict access further

### Accidental Production Exposure

1. **Disable immediately** via environment variable

2. **Deploy updated configuration**

3. **Rotate credentials** that may have been exposed

4. **Review access logs** for unauthorized usage

5. **Perform security audit**

## Reporting Security Issues

If you discover a security vulnerability in Spectra:

**DO NOT** create a public GitHub issue.

**Email**: security@akira-io.com

Include:
- Description of the vulnerability
- Steps to reproduce
- Potential impact
- Suggested fix (if any)

## Security Updates

Security updates will be released as patch versions (e.g., 1.0.1 → 1.0.2).

**Subscribe to notifications**:
- Watch the GitHub repository
- Enable Packagist notifications
- Follow release notes

## Compliance Considerations

### GDPR

- Mask personal data in responses
- Don't log sensitive information
- Provide data export capabilities

### PCI DSS

- Never expose credit card data
- Mask payment tokens
- Restrict access to authorized personnel

### SOC 2

- Implement audit logging
- Restrict access with gate controls
- Regular security reviews

## Next Steps

- [Configuration](configuration.md) - Security configuration options
- [Authentication](authentication.md) - Authentication modes
- [Extending Spectra](development/extending.md) - Secure customization
