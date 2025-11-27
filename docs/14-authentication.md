# Authentication

Spectra supports four authentication modes for executing API requests. This guide covers each mode in detail.

## Authentication Modes

### Overview

| Mode | Use Case | Authorization Required | Best For |
|------|----------|----------------------|----------|
| Current User | Test as yourself | None | Quick testing, debugging |
| Impersonate | Test as another user | `use-spectra` gate | User-specific testing |
| Bearer Token | API authentication | Token validity | API testing, external clients |
| Basic Auth | Username/password | Credentials validity | Legacy APIs, simple auth |

## Current User Mode

### Description

Execute requests as the currently authenticated user. This uses your active session.

### Usage

1. Select "Current User" in the Auth Panel
2. No additional configuration needed
3. Send your request

### How It Works

```php
// Internally uses your session
$user = Auth::guard(config('spectra.guard'))->user();
```

### Use Cases

- Quick endpoint testing
- Debugging your own permissions
- Testing authenticated routes

### Example

Testing a user profile endpoint:

```
GET /api/user/profile
Auth Mode: Current User
```

Response shows your own profile data.

### Security

- Uses your actual session
- Respects your permissions
- Changes don't persist
- No elevated privileges

## Impersonate Mode

### Description

Execute requests as a different user. Requires authorization via the `use-spectra` gate.

### Authorization

Define the gate in `AuthServiceProvider`:

```php
Gate::define('use-spectra', function ($user) {
    return $user->hasRole('developer') || $user->hasRole('admin');
});
```

### Usage

1. Select "Impersonate" in the Auth Panel
2. Enter the user identifier (ID or email)
3. Send your request

### How It Works

```php
// Finds the user
$targetUser = User::where('id', $identifier)
    ->orWhere('email', $identifier)
    ->firstOrFail();

// Sets them as authenticated
Auth::guard($guard)->setUser($targetUser);

// Execute request
$response = $kernel->handle($request);

// Restore original user
Auth::guard($guard)->setUser($originalUser);
```

### User Identifier Formats

**By ID**:
```
123
```

**By Email**:
```
john@example.com
```

**By Username** (if your User model supports it):
```php
// Customize in AuthBroker
$user = User::where('username', $identifier)->first();
```

### Use Cases

- Testing role-based permissions
- Debugging user-specific issues
- Verifying access controls
- Testing multi-tenant features

### Example

Testing an admin-only endpoint:

```
GET /api/admin/users
Auth Mode: Impersonate
User: admin@example.com
```

### Security Considerations

- Requires `use-spectra` gate approval
- Original user must be authorized
- No password required (security risk if misused)
- Audit log all impersonations
- Use only in development

**Recommended**: Add additional checks

```php
Gate::define('use-spectra', function ($user) {
    // Only allow in local environment
    if (!app()->environment('local')) {
        return false;
    }
    
    return $user->hasRole('developer');
});
```

### Limitations

- Can only impersonate existing users
- Cannot impersonate guests/unauthenticated
- May not work with some auth drivers

## Bearer Token Mode

### Description

Authenticate using a Bearer token (API token, OAuth token, etc.).

### Usage

1. Select "Bearer Token" in the Auth Panel
2. Enter the token value
3. Send your request

### How It Works

```php
// Adds Authorization header
$request->headers->set('Authorization', 'Bearer ' . $token);
```

### Token Formats

**Standard Bearer Token**:
```
eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

**Laravel Sanctum**:
```
1|abcdef123456789...
```

**Personal Access Token**:
```
your-api-token-here
```

### Use Cases

- Testing API endpoints
- Validating token authentication
- Testing OAuth flows
- Debugging API clients

### Example

Testing a protected API endpoint:

```
GET /api/v1/posts
Auth Mode: Bearer Token
Token: 1|abc123xyz789
```

### Token Generation

**Laravel Sanctum**:
```php
$token = $user->createToken('spectra-test')->plainTextToken;
```

**Laravel Passport**:
```php
$token = $user->createToken('spectra-test')->accessToken;
```

**Custom**:
```php
$token = Str::random(60);
$user->api_token = hash('sha256', $token);
$user->save();
```

### Security Considerations

- Tokens are sent in plain text over HTTP
- Use HTTPS in non-local environments
- Tokens can be intercepted if not secured
- Revoke test tokens after use
- Don't commit tokens to version control

**Best Practice**: Use short-lived tokens

```php
$token = $user->createToken('spectra-test', ['read'], now()->addHours(1));
```

### Troubleshooting

**Token Not Working**:
- Verify token is valid
- Check token hasn't expired
- Ensure Bearer prefix is included
- Verify auth guard configuration

**401 Unauthorized**:
- Token is invalid or expired
- Token is for wrong environment
- API route doesn't accept tokens

## Basic Auth Mode

### Description

Authenticate using username and password (HTTP Basic Authentication).

### Usage

1. Select "Basic Auth" in the Auth Panel
2. Enter username
3. Enter password
4. Send your request

### How It Works

```php
// Encodes credentials
$credentials = base64_encode($username . ':' . $password);

// Adds Authorization header
$request->headers->set('Authorization', 'Basic ' . $credentials);
```

### Credential Formats

**Username and Password**:
```
Username: john@example.com
Password: secret123
```

**Encoded Header**:
```
Authorization: Basic am9obkBleGFtcGxlLmNvbTpzZWNyZXQxMjM=
```

### Use Cases

- Testing legacy APIs
- Simple authentication testing
- Third-party API integration
- Basic HTTP auth endpoints

### Example

Testing a basic auth endpoint:

```
GET /api/legacy/data
Auth Mode: Basic Auth
Username: api_user
Password: api_password
```

### Security Considerations

- Credentials sent in every request
- Base64 encoding is NOT encryption
- Easily decoded if intercepted
- **Always use HTTPS**
- Not recommended for production APIs

**Warning**: Basic Auth sends credentials with every request.

### Troubleshooting

**401 Unauthorized**:
- Credentials are incorrect
- Account is locked/disabled
- Endpoint doesn't support Basic Auth

**Empty Response**:
- Check Laravel's basic auth middleware
- Verify auth configuration

## Authentication Guard

### Configuration

Spectra uses the configured authentication guard:

```php
'guard' => env('SPECTRA_GUARD', 'web'),
```

### Changing Guard

**Web Guard** (default):
```env
SPECTRA_GUARD=web
```

**API Guard**:
```env
SPECTRA_GUARD=api
```

**Custom Guard**:
```env
SPECTRA_GUARD=custom
```

### Multiple Guards

If your application uses multiple guards:

```php
// config/auth.php
'guards' => [
    'web' => [...],
    'api' => [...],
    'admin' => [...],
],
```

Configure Spectra to use the appropriate guard:

```env
SPECTRA_GUARD=admin
```

## Authorization Flow

### Gate Check Flow

```
User accesses /spectra
    → Middleware: auth
        → User is authenticated?
            → YES: Continue
            → NO: Redirect to login
    → Middleware: EnsureSpectraEnabled
        → Gate::allows('use-spectra')?
            → YES: Continue
            → NO: Abort 403
    → Controller: Show Spectra UI
```

### Execution Authorization Flow

```
User sends request
    → ExecuteController
        → Validate input
        → Check auth mode
            → Impersonate?
                → Gate::allows('use-spectra')?
                    → YES: Allow
                    → NO: Abort 403
            → Other modes: Continue
        → Execute request
        → Return response
```

## Custom Authentication

### Custom Auth Driver

Implement a custom auth driver:

```php
// In AuthBroker
public function authenticate(ExecuteCommand $command): void
{
    match ($command->authMode) {
        AuthMode::CURRENT => $this->useCurrent(),
        AuthMode::IMPERSONATE => $this->impersonate($command->authValue),
        AuthMode::BEARER => $this->useBearer($command->authValue),
        AuthMode::BASIC => $this->useBasic($command->authValue),
        AuthMode::CUSTOM => $this->useCustom($command->authValue),
    };
}

protected function useCustom(string $value): void
{
    // Your custom logic
    $user = CustomAuth::authenticate($value);
    Auth::guard($this->guard)->setUser($user);
}
```

### Extending AuthMode Enum

Add a custom auth mode:

```php
enum AuthMode: string
{
    case CURRENT = 'current';
    case IMPERSONATE = 'impersonate';
    case BEARER = 'bearer';
    case BASIC = 'basic';
    case CUSTOM = 'custom';  // Add custom mode
}
```

## Best Practices

### 1. Use Appropriate Mode

- **Development**: Current User or Impersonate
- **API Testing**: Bearer Token
- **Legacy Systems**: Basic Auth

### 2. Secure Token Storage

Never store tokens in:
- Version control
- Public documentation
- Shared configs

Store tokens in:
- Environment variables
- Secure vaults
- Encrypted storage

### 3. Limit Impersonation

```php
Gate::define('use-spectra', function ($user) {
    // Restrict impersonation
    if (request()->input('auth_mode') === 'impersonate') {
        return $user->hasRole('super-admin');
    }
    
    return $user->hasRole('developer');
});
```

### 4. Audit Logging

Log all authentication attempts:

```php
Log::info('Spectra authentication', [
    'user' => auth()->id(),
    'mode' => $authMode,
    'target' => $targetIdentifier,
    'ip' => request()->ip(),
]);
```

### 5. Token Expiration

Use short-lived tokens for testing:

```php
$token = $user->createToken('test', ['*'], now()->addHour());
```

### 6. Environment-Specific Auth

```php
// Local: Permissive
Gate::define('use-spectra', fn($user) => true);

// Staging: Restricted
Gate::define('use-spectra', fn($user) => $user->hasRole('tester'));

// Production: Disabled
Gate::define('use-spectra', fn($user) => false);
```

## Troubleshooting

### Cannot Access Spectra

**Check authentication**:
```bash
php artisan tinker
>>> auth()->check()  // Should return true
>>> auth()->user()   // Should return your user
```

**Check gate**:
```bash
php artisan tinker
>>> Gate::allows('use-spectra')  // Should return true
```

### Impersonation Fails

**Check user exists**:
```bash
php artisan tinker
>>> User::where('email', 'test@example.com')->exists()
```

**Check gate authorization**:
```php
if (!Gate::allows(config('spectra.impersonation_gate'))) {
    // You're not authorized
}
```

### Bearer Token Fails

**Check token format**:
- Must not include "Bearer" prefix in input
- Spectra adds it automatically

**Check token validity**:
```bash
php artisan tinker
>>> PersonalAccessToken::findToken('your-token')
```

### Basic Auth Fails

**Check middleware**:
- Ensure endpoint supports basic auth
- Verify credentials are correct

**Check guard**:
- Basic auth typically uses `api` guard
- Configure: `SPECTRA_GUARD=api`

## Next Steps

- [Security](security.md) - Security best practices
- [Features](features/request-execution.md) - Request execution details
- [Quick Start](quick-start.md) - Start using authentication modes
