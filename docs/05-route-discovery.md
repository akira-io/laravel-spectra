# Route Discovery

Spectra automatically discovers and analyzes all routes in your Laravel application, providing comprehensive metadata for each endpoint.

## Overview

The route discovery system scans your application's route collection and extracts:
- HTTP methods
- URI patterns
- Route names
- Controller actions
- Middleware
- Parameters (path, query, body)

## How It Works

### 1. Route Scanning

When you load Spectra, the `RouteScanner` service scans Laravel's route collection:

```php
$routes = Route::getRoutes();
foreach ($routes as $route) {
    $metadata = $this->extractMetadata($route);
}
```

### 2. Metadata Extraction

For each route, Spectra extracts:

**Basic Information**:
- Method (GET, POST, PUT, PATCH, DELETE)
- URI (/api/users/{id})
- Name (users.show)
- Action (UserController@show)

**Middleware**:
- All middleware applied to the route
- Middleware groups expanded
- Sorted by execution order

**Parameters**:
- Path parameters from URI patterns
- Query parameters from documentation
- Body parameters from FormRequests

### 3. Parameter Analysis

Spectra analyzes three types of parameters:

#### Path Parameters

Extracted from URI patterns:

```php
// Route: /api/users/{id}/posts/{post}
// Parameters extracted:
[
    'id' => ['type' => 'integer', 'required' => true],
    'post' => ['type' => 'integer', 'required' => true],
]
```

#### Query Parameters

Detected from route documentation or FormRequests:

```php
// Route: GET /api/users
// Query parameters:
[
    'page' => ['type' => 'integer', 'required' => false],
    'per_page' => ['type' => 'integer', 'required' => false],
    'search' => ['type' => 'string', 'required' => false],
]
```

#### Body Parameters

Extracted from FormRequest validation rules:

```php
// StoreUserRequest
public function rules()
{
    return [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8',
    ];
}

// Parameters extracted:
[
    'name' => ['type' => 'string', 'required' => true, 'max' => 255],
    'email' => ['type' => 'string', 'required' => true, 'format' => 'email'],
    'password' => ['type' => 'string', 'required' => true, 'min' => 8],
]
```

## Route Metadata Structure

### RouteMeta DTO

```php
readonly class RouteMeta
{
    public function __construct(
        public string $method,       // HTTP method
        public string $uri,          // URI pattern
        public string $name,         // Route name
        public string $action,       // Controller action
        public array $middleware,    // Middleware stack
        public array $parameters,    // All parameters
    ) {}
}
```

### Parameter Metadata

```php
readonly class ParameterMeta
{
    public function __construct(
        public string $name,         // Parameter name
        public string $type,         // Data type
        public bool $required,       // Is required?
        public mixed $default,       // Default value
    ) {}
}
```

## Supported Route Types

### Standard Routes

```php
Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
```

Fully supported with all metadata.

### Resource Routes

```php
Route::resource('users', UserController::class);
```

Expands to individual routes:
- GET /users (index)
- POST /users (store)
- GET /users/{user} (show)
- PUT /users/{user} (update)
- DELETE /users/{user} (destroy)

### API Resource Routes

```php
Route::apiResource('users', UserController::class);
```

Like resource routes, excluding create/edit.

### Nested Routes

```php
Route::resource('users.posts', PostController::class);
```

Properly handles nested parameters:
- /users/{user}/posts/{post}

### Route Groups

```php
Route::prefix('api/v1')->group(function () {
    Route::get('/users', [UserController::class, 'index']);
});
```

Includes prefix in URI and group middleware.

## FormRequest Integration

### Automatic Detection

Spectra automatically detects FormRequests:

```php
class StoreUserRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string',
            'email' => 'required|email',
        ];
    }
}

class UserController
{
    public function store(StoreUserRequest $request)
    {
        // Spectra detects StoreUserRequest
    }
}
```

### Rule Extraction

Extracts validation rules:

```php
// Input
'email' => 'required|email|unique:users,email'

// Extracted
[
    'name' => 'email',
    'type' => 'string',
    'format' => 'email',
    'required' => true,
    'unique' => true,
]
```

### Nested Rules

Supports nested validation rules:

```php
public function rules()
{
    return [
        'user.name' => 'required|string',
        'user.email' => 'required|email',
        'tags.*' => 'string',
    ];
}

// Extracted structure
[
    'user' => [
        'type' => 'object',
        'properties' => [
            'name' => ['type' => 'string', 'required' => true],
            'email' => ['type' => 'string', 'format' => 'email'],
        ],
    ],
    'tags' => [
        'type' => 'array',
        'items' => ['type' => 'string'],
    ],
]
```

## Route Filtering

### By Method

Filter routes by HTTP method:

```typescript
const getRoutes = routes.filter(r => r.method === 'GET');
const postRoutes = routes.filter(r => r.method === 'POST');
```

### By URI Pattern

Filter by URI:

```typescript
const apiRoutes = routes.filter(r => r.uri.startsWith('/api'));
const userRoutes = routes.filter(r => r.uri.includes('/users'));
```

### By Middleware

Filter by applied middleware:

```typescript
const authRoutes = routes.filter(r => 
    r.middleware.includes('auth')
);
```

### By Name

Filter by route name:

```typescript
const route = routes.find(r => r.name === 'users.store');
```

## Endpoint Tree Display

### Hierarchical Structure

Routes are displayed in a hierarchical tree:

```
api/
├── v1/
│   ├── users/
│   │   ├── GET /api/v1/users
│   │   ├── POST /api/v1/users
│   │   └── {id}/
│   │       ├── GET /api/v1/users/{id}
│   │       ├── PUT /api/v1/users/{id}
│   │       └── DELETE /api/v1/users/{id}
│   └── posts/
│       └── ...
```

### Search Functionality

Quick search with `Cmd/Ctrl + K`:

1. Press shortcut
2. Type search query
3. Results filter in real-time
4. Press Enter to select

**Search Criteria**:
- URI pattern
- Route name
- Controller action
- HTTP method

### Color Coding

Methods are color-coded:
- **GET**: Blue
- **POST**: Green
- **PUT/PATCH**: Yellow
- **DELETE**: Red

## Performance Considerations

### Caching

Route discovery runs once per page load and leverages Laravel's route caching:

```bash
# Cache routes for better performance
php artisan route:cache
```

### Lazy Loading

Routes are loaded lazily in the UI for better initial load time.

### Filtering

Client-side filtering ensures fast search without server roundtrips.

## Excluding Routes

### Internal Routes

Spectra automatically excludes internal routes:
- Framework routes (/_ignition)
- Telescope routes (/telescope)
- Horizon routes (/horizon)

### Custom Exclusions

Exclude specific patterns:

```php
// In RouteScanner
protected function shouldInclude(Route $route): bool
{
    $excludePatterns = [
        '_ignition/*',
        'telescope/*',
        'horizon/*',
        'admin/internal/*',
    ];
    
    foreach ($excludePatterns as $pattern) {
        if (Str::is($pattern, $route->uri())) {
            return false;
        }
    }
    
    return true;
}
```

## Advanced Features

### Parameter Type Inference

Infers parameter types from:
1. Route model binding
2. Type hints
3. Validation rules
4. Default values

```php
// Route
Route::get('/users/{user:uuid}', ...);

// Inferred
['user' => ['type' => 'string', 'format' => 'uuid']]
```

### Middleware Analysis

Displays middleware details:
- Middleware name
- Parameters
- Execution order

### Documentation Comments

Extracts PHPDoc comments:

```php
/**
 * Get all users
 * 
 * @queryParam page int Page number
 * @queryParam per_page int Items per page
 */
public function index() {}
```

## Troubleshooting

### Routes Not Appearing

**Clear route cache**:
```bash
php artisan route:clear
php artisan route:cache
```

**Check route registration**:
```bash
php artisan route:list
```

### Parameters Not Detected

**Check FormRequest**:
- Ensure controller method type-hints FormRequest
- Verify rules() method exists
- Check rule syntax

**Manual inspection**:
```bash
php artisan tinker
>>> app(App\Http\Requests\StoreUserRequest::class)->rules()
```

### Incorrect Metadata

**Verify route definition**:
```bash
php artisan route:list --path=users
```

**Check middleware**:
```bash
php artisan route:list --columns=method,uri,middleware
```

## API Reference

### DiscoverRoutesAction

```php
$action = app(DiscoverRoutesAction::class);
$routes = $action->execute();
```

**Returns**: Array of RouteMeta objects

### RouteScanner

```php
$scanner = app(RouteScanner::class);
$routes = $scanner->scanRoutes();
```

**Methods**:
- `scanRoutes(): array` - Scan all routes
- `getRouteMeta(Route $route): RouteMeta` - Get metadata for single route

## Next Steps

- [Schema Generation](schema-generation.md) - JSON Schema from validation rules
- [Request Execution](request-execution.md) - Execute discovered routes
- [UI: Endpoint Tree](../ui/endpoint-tree.md) - Using the endpoint browser
