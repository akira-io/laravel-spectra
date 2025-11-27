# Cookie Inspector

Spectra includes a cookie inspector that allows you to view, decrypt, and analyze Laravel cookies during development.

## Overview

The cookie inspector:
- Lists all cookies in the request
- Decrypts Laravel encrypted cookies
- Displays cookie metadata
- Shows serialized data structures
- Helps debug session issues

## Features

### 1. Cookie Listing

View all cookies sent with requests:
- Cookie names
- Raw values
- Decrypted values (when applicable)
- Expiration dates
- Domain and path
- Secure and HttpOnly flags

### 2. Laravel Cookie Decryption

Automatically decrypts cookies encrypted by Laravel:

```php
// Original value (encrypted)
"eyJpdiI6IkRBM3ZHTmhMMzZ..."

// Decrypted value
{"user_id": 123, "session_token": "abc..."}
```

### 3. Session Data

View session data stored in cookies:
- User ID
- CSRF token
- Previous URL
- Flash messages
- Custom session data

### 4. Metadata Display

Shows cookie attributes:
- **Name**: Cookie identifier
- **Value**: Raw or decrypted value
- **Domain**: Cookie domain
- **Path**: Cookie path
- **Expires**: Expiration timestamp
- **Secure**: HTTPS only flag
- **HttpOnly**: JavaScript access flag
- **SameSite**: CSRF protection setting

## How It Works

### Cookie Inspection Flow

```
User clicks "Refresh Cookies"
    → CookieController::index()
        → ListCookiesAction::execute()
            → CookieInspector::inspect()
                → Get all cookies from request
                → Attempt decryption for each
                → Extract metadata
                → Return cookie data
            → CookieResource::collection()
        → Return JSON
    → React updates CookiePanel
```

### Decryption Process

```php
public function decrypt(string $value): mixed
{
    try {
        // Attempt Laravel decryption
        return $this->encrypter->decrypt($value);
    } catch (DecryptException $e) {
        // Not encrypted or invalid
        return $value;
    }
}
```

## Cookie Types

### 1. Laravel Session Cookie

**Name**: `laravel_session` (or configured name)

**Content**:
```json
{
  "_token": "csrf-token-here",
  "_previous": {
    "url": "http://localhost/dashboard"
  },
  "login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d": 123
}
```

**Purpose**:
- Stores session ID
- Contains CSRF token
- Tracks user authentication
- Stores flash messages

### 2. Remember Token Cookie

**Name**: `remember_web_{hash}`

**Content**:
```
123|long-random-token-here
```

**Purpose**:
- "Remember me" functionality
- Persistent authentication
- Auto-login on return visits

### 3. XSRF Token Cookie

**Name**: `XSRF-TOKEN`

**Content**:
```
csrf-token-value
```

**Purpose**:
- CSRF protection
- Sent with AJAX requests
- Validated by Laravel

### 4. Custom Application Cookies

Any cookies set by your application:

```php
Cookie::queue('preferences', json_encode($data), 43200);
```

## Using the Inspector

### Accessing the Inspector

1. Open Spectra at `/spectra`
2. Click the "Cookies" tab or button
3. View current cookies
4. Click "Refresh" to reload

### Viewing Cookie Details

Click on a cookie to see:
- **Name**: Cookie identifier
- **Value**: Decrypted or raw value
- **Encrypted**: Whether cookie is encrypted
- **Expires**: Expiration date
- **Domain**: Cookie domain
- **Path**: Cookie path
- **Flags**: Secure, HttpOnly, SameSite

### Refreshing Cookies

Click "Refresh Cookies" to reload the list with current request cookies.

### Copying Cookie Values

Click the copy icon to copy decrypted value to clipboard.

## Cookie Metadata

### CookieResource Structure

```php
[
    'name' => 'laravel_session',
    'value' => 'decrypted-or-raw-value',
    'encrypted' => true,
    'expires' => 1704067200,
    'domain' => 'localhost',
    'path' => '/',
    'secure' => false,
    'httpOnly' => true,
    'sameSite' => 'lax',
]
```

### Example Response

```json
{
  "data": [
    {
      "name": "laravel_session",
      "value": {
        "_token": "abc123xyz",
        "login_web_59ba36...": 123
      },
      "encrypted": true,
      "expires": 1704067200,
      "domain": "localhost",
      "path": "/",
      "secure": false,
      "httpOnly": true,
      "sameSite": "lax"
    },
    {
      "name": "XSRF-TOKEN",
      "value": "csrf-token-here",
      "encrypted": false,
      "expires": null,
      "domain": "localhost",
      "path": "/",
      "secure": false,
      "httpOnly": false,
      "sameSite": "lax"
    }
  ]
}
```

## Debugging with Cookies

### Session Issues

**Problem**: User keeps getting logged out

**Solution**:
1. Open cookie inspector
2. Check `laravel_session` cookie
3. Verify session data is present
4. Check expiration time
5. Ensure cookie domain matches

### Authentication Issues

**Problem**: "Remember me" not working

**Solution**:
1. Check for `remember_web_*` cookie
2. Verify token format (id|token)
3. Check expiration (should be long-lived)
4. Ensure cookie is not expired

### CSRF Issues

**Problem**: CSRF token mismatch

**Solution**:
1. Check `XSRF-TOKEN` cookie exists
2. Verify token matches session token
3. Ensure cookie is sent with requests
4. Check SameSite setting

## Security Considerations

### Encryption

Laravel automatically encrypts cookies (except exceptions):

```php
// config/session.php
'encrypt' => true,  // Encrypt session cookie

// app/Http/Middleware/EncryptCookies.php
protected $except = [
    'cookie_name',  // Don't encrypt
];
```

### Decryption in Spectra

Spectra can decrypt cookies because:
- It runs in the same Laravel application
- Uses the same APP_KEY
- Has access to the encrypter service

**Important**: This is why Spectra must never be enabled in production.

### Cookie Security Flags

**Secure**:
- Cookie only sent over HTTPS
- Prevents interception
- Set `true` in production

**HttpOnly**:
- Cookie not accessible via JavaScript
- Prevents XSS attacks
- Laravel sets by default

**SameSite**:
- Prevents CSRF attacks
- Options: `strict`, `lax`, `none`
- Laravel default: `lax`

## Configuration

### Session Configuration

```php
// config/session.php
'driver' => 'file',              // Session driver
'lifetime' => 120,               // Minutes
'expire_on_close' => false,      // Expire when browser closes
'encrypt' => true,               // Encrypt session cookie
'cookie' => 'laravel_session',   // Cookie name
'secure' => true,                // HTTPS only
'http_only' => true,             // Not accessible via JS
'same_site' => 'lax',            // CSRF protection
```

### Cookie Configuration

```php
// config/session.php or config/cookie.php
'domain' => env('SESSION_DOMAIN'),
'path' => '/',
'secure' => env('SESSION_SECURE_COOKIE', false),
'http_only' => true,
'same_site' => 'lax',
```

## API Reference

### List Cookies Endpoint

**Endpoint**: `GET /spectra/cookies`

**Response**:
```json
{
  "data": [
    {
      "name": "cookie_name",
      "value": "cookie_value",
      "encrypted": true,
      "expires": 1704067200,
      "domain": "localhost",
      "path": "/",
      "secure": false,
      "httpOnly": true,
      "sameSite": "lax"
    }
  ]
}
```

### ListCookiesAction

```php
$action = app(ListCookiesAction::class);
$cookies = $action->execute();
```

### CookieInspector Service

```php
$inspector = app(CookieInspector::class);

// Get all cookies
$cookies = $inspector->inspect();

// Decrypt single cookie
$decrypted = $inspector->decrypt($encryptedValue);

// Check if encrypted
$isEncrypted = $inspector->isEncrypted($cookieName);
```

## Advanced Usage

### Custom Cookie Analysis

Extend CookieInspector:

```php
class CustomCookieInspector extends CookieInspector
{
    public function inspect(): array
    {
        $cookies = parent::inspect();
        
        // Add custom analysis
        foreach ($cookies as &$cookie) {
            $cookie['custom_data'] = $this->analyzeCustomData($cookie);
        }
        
        return $cookies;
    }
    
    protected function analyzeCustomData(array $cookie): array
    {
        // Your custom logic
        return [...];
    }
}
```

Bind in service provider:

```php
$this->app->bind(CookieInspector::class, CustomCookieInspector::class);
```

### Cookie Filtering

Filter cookies in UI:

```typescript
// Show only session cookies
const sessionCookies = cookies.filter(c => 
    c.name.includes('session')
);

// Show only encrypted cookies
const encryptedCookies = cookies.filter(c => 
    c.encrypted
);
```

## Troubleshooting

### Cookies Not Appearing

**Check request**:
- Ensure cookies are being sent
- Verify cookie domain matches
- Check browser settings

**Clear and retry**:
```bash
# Clear application cache
php artisan cache:clear
php artisan config:clear

# Clear browser cookies
# Then login again
```

### Decryption Fails

**Check APP_KEY**:
```bash
php artisan key:generate
```

**Verify encryption**:
```php
// Check if cookie should be encrypted
protected $except = [
    'cookie_name',  // This won't be encrypted
];
```

### Session Data Missing

**Check session driver**:
```php
// config/session.php
'driver' => 'file',  // Or 'database', 'redis', etc.
```

**Verify session files**:
```bash
ls -la storage/framework/sessions/
```

## Best Practices

1. **Development Only**: Only use in local environment
2. **Secure Cookies**: Always use secure cookies in production
3. **HttpOnly**: Keep HttpOnly enabled for session cookies
4. **SameSite**: Use `strict` or `lax` for CSRF protection
5. **Encryption**: Keep session encryption enabled
6. **Regular Cleanup**: Clear old session files

## Privacy Considerations

### Data Exposure

Cookie inspector shows:
- Session data
- User IDs
- Authentication tokens
- Custom application data

**Never**:
- Screenshot cookie data
- Share session tokens
- Commit cookie values to version control
- Use in production

### Compliance

Ensure cookie usage complies with:
- **GDPR**: EU privacy regulation
- **CCPA**: California privacy law
- **ePrivacy**: EU cookie directive

## Next Steps

- [Request Execution](request-execution.md) - Execute requests with cookies
- [Security](../security.md) - Security best practices
- [Troubleshooting](../advanced/troubleshooting.md) - Debug cookie issues
