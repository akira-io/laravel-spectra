# Request Execution

Spectra executes API requests internally through Laravel's HTTP kernel, providing a secure and efficient way to test your endpoints.

## Overview

Request execution features:
- Internal execution (no external HTTP calls)
- Multiple authentication modes
- Header management
- Response processing
- Execution timing
- Security controls

## How It Works

### Execution Flow

```
User Input
    → Validate request
    → Set up authentication
    → Create internal request
    → Strip sensitive headers
    → Execute through kernel
    → Process response
    → Mask sensitive fields
    → Return result
```

### Internal vs External

**Internal Execution** (Spectra):
```php
$request = Request::create($uri, $method, $parameters);
$response = $kernel->handle($request);
```

**External Execution** (HTTP client):
```php
Http::post('https://api.example.com/endpoint', $parameters);
```

**Why Internal?**
- No network overhead
- Same application state
- Access to internal services
- Respects middleware
- Maintains session context

## ExecuteCommand

### Structure

```php
readonly class ExecuteCommand
{
    public function __construct(
        public string $method,      // GET, POST, PUT, PATCH, DELETE
        public string $uri,         // /api/users/{id}
        public array $parameters,   // Request data
        public array $headers,      // Custom headers
        public AuthMode $authMode,  // Authentication mode
        public ?string $authValue,  // Auth credentials
    ) {}
}
```

### Example

```php
$command = new ExecuteCommand(
    method: 'POST',
    uri: '/api/users',
    parameters: [
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ],
    headers: [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ],
    authMode: AuthMode::CURRENT,
    authValue: null,
);
```

## Parameters

### Path Parameters

Substitute path placeholders:

```
URI: /api/users/{id}
Parameters: ['id' => 123]
Result: /api/users/123
```

### Query Parameters

Append to URI:

```
URI: /api/users
Parameters: ['page' => 1, 'per_page' => 10]
Result: /api/users?page=1&per_page=10
```

### Body Parameters

Sent in request body:

```
Method: POST
URI: /api/users
Parameters: {
    "name": "John Doe",
    "email": "john@example.com"
}
```

### Mixed Parameters

```
URI: /api/users/{id}/posts
Parameters: {
    "id": 123,              // Path parameter
    "page": 1,              // Query parameter
    "title": "New Post",    // Body parameter
    "content": "..."        // Body parameter
}

Result: /api/users/123/posts?page=1
Body: {"title": "New Post", "content": "..."}
```

## Headers

### Default Headers

Automatically added:

```php
[
    'Accept' => 'application/json',
    'Content-Type' => 'application/json',
    'X-Requested-With' => 'XMLHttpRequest',
]
```

### Custom Headers

Add custom headers in the UI:

```
X-Custom-Header: value
X-API-Version: v1
```

### Header Stripping

Sensitive headers are removed:

```php
// Stripped headers (configurable)
[
    'authorization',  // Unless using Bearer/Basic auth
    'cookie',
    'x-api-key',
    'x-csrf-token',
]
```

**Why?** Prevents accidental credential exposure.

### Authorization Headers

Added based on auth mode:

```php
// Bearer Token
'Authorization: Bearer abc123xyz'

// Basic Auth
'Authorization: Basic dXNlcjpwYXNz'
```

## Request Processing

### 1. Validation

Validates input via `ExecuteRequest`:

```php
public function rules()
{
    return [
        'method' => 'required|in:GET,POST,PUT,PATCH,DELETE',
        'uri' => 'required|string',
        'parameters' => 'array',
        'headers' => 'array',
        'auth_mode' => 'required|in:current,impersonate,bearer,basic',
        'auth_value' => 'nullable|string',
    ];
}
```

### 2. Authentication Setup

Sets up authentication based on mode:

```php
$this->authBroker->authenticate($command);
```

See [Authentication](../authentication.md) for details.

### 3. Request Creation

Creates internal request:

```php
$request = Request::create(
    uri: $command->uri,
    method: $command->method,
    parameters: $command->parameters,
    cookies: request()->cookies->all(),
    files: $command->files ?? [],
    server: $this->prepareServerVariables(),
    content: $this->prepareContent($command),
);
```

### 4. Header Processing

```php
// Add custom headers
foreach ($command->headers as $key => $value) {
    $request->headers->set($key, $value);
}

// Strip sensitive headers
$request->headers->remove('authorization');
$request->headers->remove('cookie');

// Add back auth header if needed
if ($command->authMode === AuthMode::BEARER) {
    $request->headers->set('Authorization', 'Bearer ' . $command->authValue);
}
```

### 5. Execution

Executes through kernel:

```php
$startTime = microtime(true);

$response = $this->kernel->handle($request);

$executionTime = microtime(true) - $startTime;
```

### 6. Response Processing

```php
$result = new ExecuteResult(
    statusCode: $response->getStatusCode(),
    headers: $response->headers->all(),
    body: $this->processBody($response),
    executionTime: $executionTime,
);
```

## ExecuteResult

### Structure

```php
readonly class ExecuteResult
{
    public function __construct(
        public int $statusCode,       // HTTP status code
        public array $headers,        // Response headers
        public mixed $body,           // Response body
        public float $executionTime,  // Execution time in seconds
    ) {}
}
```

### Example

```json
{
  "status_code": 201,
  "headers": {
    "content-type": ["application/json"],
    "cache-control": ["no-cache, private"]
  },
  "body": {
    "data": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    }
  },
  "execution_time": 0.125
}
```

## Response Processing

### JSON Responses

Automatically parsed:

```php
$body = json_decode($response->getContent(), true);
```

### HTML Responses

Returned as string:

```php
$body = $response->getContent();
```

### File Downloads

Handled appropriately:

```php
if ($response instanceof BinaryFileResponse) {
    $body = [
        'type' => 'file',
        'name' => $response->getFile()->getFilename(),
        'size' => $response->getFile()->getSize(),
    ];
}
```

### Error Responses

Includes error details:

```json
{
  "status_code": 422,
  "body": {
    "message": "The given data was invalid.",
    "errors": {
      "email": ["The email field is required."]
    }
  }
}
```

## Field Masking

### Configuration

```php
'mask_fields' => [
    'password',
    'token',
    'authorization',
    'api_key',
    'secret',
],
```

### Masking Logic

```php
protected function maskFields(array $body): array
{
    foreach ($body as $key => $value) {
        if (in_array(strtolower($key), $this->maskFields)) {
            $body[$key] = '***MASKED***';
        } elseif (is_array($value)) {
            $body[$key] = $this->maskFields($value);
        }
    }
    
    return $body;
}
```

### Example

**Original**:
```json
{
  "user": {
    "name": "John Doe",
    "email": "john@example.com",
    "api_key": "sk_live_abc123xyz"
  }
}
```

**Masked**:
```json
{
  "user": {
    "name": "John Doe",
    "email": "john@example.com",
    "api_key": "***MASKED***"
  }
}
```

## Rate Limiting

### Configuration

```php
'rate_limit' => [
    'max' => 60,           // Maximum requests
    'per_minutes' => 1,    // Time window
],
```

### Implementation

```php
Route::post('/execute', ExecuteController::class)
    ->middleware('throttle:spectra-execute');
```

### Rate Limit Response

```json
{
  "message": "Too Many Attempts.",
  "retry_after": 60
}
```

## Performance

### Execution Timing

Tracks execution time:

```php
$startTime = microtime(true);
$response = $kernel->handle($request);
$executionTime = microtime(true) - $startTime;
```

Displayed in UI:
```
Execution Time: 125ms
```

### Optimization Tips

1. **Cache routes**: `php artisan route:cache`
2. **Optimize queries**: Use eager loading
3. **Enable OPcache**: For PHP optimization
4. **Database indexing**: For query performance

## Error Handling

### Validation Errors

```json
{
  "status_code": 422,
  "body": {
    "message": "The given data was invalid.",
    "errors": {
      "name": ["The name field is required."],
      "email": ["The email must be a valid email address."]
    }
  }
}
```

### Authorization Errors

```json
{
  "status_code": 403,
  "body": {
    "message": "This action is unauthorized."
  }
}
```

### Server Errors

```json
{
  "status_code": 500,
  "body": {
    "message": "Server Error",
    "exception": "ErrorException",
    "file": "/app/Http/Controllers/UserController.php",
    "line": 42
  }
}
```

## Security Considerations

### 1. Internal Only

Requests never leave the server:
- No DNS resolution
- No network calls
- No external data leakage

### 2. Authentication Isolation

Each request uses isolated auth:
- Doesn't affect user's session
- Restored after execution
- No persistent changes

### 3. Header Stripping

Sensitive headers removed:
- Prevents credential leakage
- Configurable list
- Applied before execution

### 4. Field Masking

Sensitive data masked:
- Recursive masking
- Configurable fields
- Applied to responses

### 5. Rate Limiting

Prevents abuse:
- 60 requests/minute default
- Per-user limiting
- Configurable thresholds

## Advanced Usage

### File Uploads

```php
$command = new ExecuteCommand(
    method: 'POST',
    uri: '/api/uploads',
    parameters: [
        'title' => 'My File',
    ],
    files: [
        'file' => UploadedFile::fake()->image('photo.jpg'),
    ],
    headers: [],
    authMode: AuthMode::CURRENT,
    authValue: null,
);
```

### Multipart Requests

Automatically handled for file uploads:

```php
Content-Type: multipart/form-data; boundary=----WebKitFormBoundary
```

### Custom Content Types

```php
$command = new ExecuteCommand(
    method: 'POST',
    uri: '/api/xml',
    parameters: ['xml' => '<root><item>value</item></root>'],
    headers: [
        'Content-Type' => 'application/xml',
    ],
    authMode: AuthMode::CURRENT,
    authValue: null,
);
```

## Troubleshooting

### Request Fails

**Check authentication**:
- Verify auth mode is correct
- Ensure credentials are valid
- Check gate authorization

**Check parameters**:
- Verify required fields are present
- Check parameter types
- Validate against schema

**Check endpoint**:
- Ensure route exists
- Verify method is allowed
- Check middleware requirements

### Slow Execution

**Profile the request**:
- Check database queries
- Identify slow operations
- Use Laravel Debugbar

**Optimize**:
- Cache results
- Eager load relationships
- Index database columns

### Rate Limited

**Increase limits** (development only):
```php
'rate_limit' => [
    'max' => 120,
    'per_minutes' => 1,
],
```

**Wait for reset**:
- Rate limits reset after time window
- Check `Retry-After` header

## API Reference

### Execute Endpoint

**Endpoint**: `POST /spectra/execute`

**Request**:
```json
{
  "method": "POST",
  "uri": "/api/users",
  "parameters": {
    "name": "John Doe",
    "email": "john@example.com"
  },
  "headers": {
    "Accept": "application/json"
  },
  "auth_mode": "current",
  "auth_value": null
}
```

**Response**:
```json
{
  "status_code": 201,
  "headers": {...},
  "body": {...},
  "execution_time": 0.125
}
```

### ExecuteRequestAction

```php
$action = app(ExecuteRequestAction::class);

$command = new ExecuteCommand(...);
$result = $action->execute($command);
```

## Next Steps

- [Authentication](../authentication.md) - Authentication modes
- [Response Viewer](../ui/response-viewer.md) - Viewing responses
- [Security](../security.md) - Security features
