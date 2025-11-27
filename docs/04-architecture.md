# Architecture

This document provides an in-depth look at Spectra's architecture, design patterns, and component organization.

## Overview

Spectra follows a modular, layered architecture designed for maintainability, testability, and extensibility.

```
┌─────────────────────────────────────────┐
│         Frontend (React + Inertia)      │
│  - EndpointTree  - RequestBuilder       │
│  - ResponseViewer - Collections         │
└────────────────┬────────────────────────┘
                 │ Inertia.js
┌────────────────▼────────────────────────┐
│           HTTP Layer (Laravel)          │
│  - Controllers  - Resources             │
│  - Middleware   - FormRequests          │
└────────────────┬────────────────────────┘
                 │
┌────────────────▼────────────────────────┐
│          Business Logic Layer           │
│  - Actions      - Services              │
│  - DTOs         - Builders              │
└─────────────────────────────────────────┘
```

## Core Components

### 1. Data Transfer Objects (DTOs)

Located in `src/Dto/`, DTOs are immutable data containers that ensure type safety across layers.

#### AuthMode Enum
```php
enum AuthMode: string
{
    case CURRENT = 'current';
    case IMPERSONATE = 'impersonate';
    case BEARER = 'bearer';
    case BASIC = 'basic';
}
```

Represents the four authentication modes available in Spectra.

#### ExecuteCommand
```php
readonly class ExecuteCommand
{
    public function __construct(
        public string $method,
        public string $uri,
        public array $parameters,
        public array $headers,
        public AuthMode $authMode,
        public ?string $authValue,
    ) {}
}
```

Encapsulates a request execution command.

#### ExecuteResult
```php
readonly class ExecuteResult
{
    public function __construct(
        public int $statusCode,
        public array $headers,
        public mixed $body,
        public float $executionTime,
    ) {}
}
```

Contains the result of a request execution.

#### RouteMeta
```php
readonly class RouteMeta
{
    public function __construct(
        public string $method,
        public string $uri,
        public string $name,
        public string $action,
        public array $middleware,
        public array $parameters,
    ) {}
}
```

Metadata about a discovered route.

#### ParameterMeta
```php
readonly class ParameterMeta
{
    public function __construct(
        public string $name,
        public string $type,
        public bool $required,
        public mixed $default,
    ) {}
}
```

Metadata about a route parameter.

#### SchemaSpec
```php
readonly class SchemaSpec
{
    public function __construct(
        public string $schema,
        public array $properties,
        public array $required,
    ) {}
}
```

JSON Schema specification for a route.

### 2. Services

Located in `src/Services/`, services contain the core business logic.

#### RouteScanner
**Purpose**: Discovers and analyzes all application routes.

**Responsibilities**:
- Scan Laravel's route collection
- Extract route metadata
- Identify parameters and their types
- Resolve FormRequest associations

**Key Methods**:
- `scanRoutes(): array` - Returns all discoverable routes
- `getRouteMeta(Route $route): RouteMeta` - Extracts metadata from a route

#### SchemaBuilder
**Purpose**: Generates JSON Schema (2020-12) from Laravel validation rules.

**Responsibilities**:
- Convert validation rules to JSON Schema
- Handle complex rule types
- Generate property schemas
- Determine required fields

**Key Methods**:
- `build(array $rules): SchemaSpec` - Builds schema from rules
- `convertRule(string $rule): array` - Converts single rule to schema property

**Supported Rules**:
- Types: `string`, `integer`, `numeric`, `boolean`, `array`, `object`
- Formats: `email`, `url`, `date`, `uuid`, `ip`, `ipv4`, `ipv6`
- Constraints: `min`, `max`, `between`, `size`, `in`, `regex`
- Files: `file`, `image`, `mimes`, `mimetypes`
- Modifiers: `required`, `nullable`, `sometimes`

#### AuthBroker
**Purpose**: Handles authentication for request execution.

**Responsibilities**:
- Authenticate requests based on mode
- Impersonate users securely
- Validate Bearer tokens
- Handle Basic authentication

**Key Methods**:
- `authenticate(ExecuteCommand $command): void` - Sets up authentication
- `impersonate(string $identifier): User` - Impersonates a user
- `validateBearerToken(string $token): bool` - Validates API token

#### RequestProxy
**Purpose**: Executes requests internally through Laravel's kernel.

**Responsibilities**:
- Create internal requests
- Execute through HTTP kernel
- Strip sensitive headers
- Mask sensitive response fields
- Track execution time

**Key Methods**:
- `execute(ExecuteCommand $command): ExecuteResult` - Executes request
- `stripHeaders(array $headers): array` - Removes sensitive headers
- `maskFields(array $body): array` - Masks sensitive fields

#### CookieInspector
**Purpose**: Inspects and decrypts Laravel cookies.

**Responsibilities**:
- List all cookies in the request
- Decrypt encrypted cookies
- Parse cookie metadata
- Handle serialized values

**Key Methods**:
- `inspect(): array` - Returns all cookies with metadata
- `decrypt(string $value): mixed` - Decrypts cookie value

#### BodyParameterExtractor
**Purpose**: Extracts body parameters from route actions.

**Responsibilities**:
- Resolve FormRequest classes
- Extract validation rules
- Determine parameter types

**Key Methods**:
- `extract(Route $route): array` - Extracts parameters from route

#### FakerValueGenerator
**Purpose**: Generates realistic example values for parameters.

**Responsibilities**:
- Generate values based on parameter type
- Use Faker for realistic data
- Handle special formats (email, URL, etc.)

**Key Methods**:
- `generate(ParameterMeta $parameter): mixed` - Generates example value

### 3. Actions

Located in `src/Actions/`, actions are single-purpose operations that orchestrate services.

#### DiscoverRoutesAction
**Purpose**: Orchestrates route discovery.

```php
public function execute(): array
{
    return $this->routeScanner->scanRoutes();
}
```

#### BuildSchemaAction
**Purpose**: Orchestrates schema generation.

```php
public function execute(array $rules): SchemaSpec
{
    return $this->schemaBuilder->build($rules);
}
```

#### ExecuteRequestAction
**Purpose**: Orchestrates request execution.

```php
public function execute(ExecuteCommand $command): ExecuteResult
{
    $this->authBroker->authenticate($command);
    return $this->requestProxy->execute($command);
}
```

#### ListCookiesAction
**Purpose**: Orchestrates cookie inspection.

```php
public function execute(): array
{
    return $this->cookieInspector->inspect();
}
```

### 4. HTTP Layer

Located in `src/Http/`, this layer handles HTTP concerns.

#### Controllers

**SpectraController**
- Renders the main Inertia page
- Passes initial data to frontend

**SchemaController**
- Generates JSON Schema for endpoints
- Returns schema as JSON

**ExecuteController**
- Executes requests
- Validates input via ExecuteRequest
- Returns ExecuteResult

**CookieController**
- Lists and inspects cookies
- Returns decrypted values

#### Resources

Transform DTOs to JSON responses:

- **RouteResource**: Transforms RouteMeta
- **SchemaResource**: Transforms SchemaSpec
- **ExecuteResultResource**: Transforms ExecuteResult
- **CookieResource**: Transforms cookie data

#### Middleware

**EnsureSpectraEnabled**
- Checks if Spectra is enabled
- Verifies environment restrictions
- Authorizes via gate
- Returns 404 if disabled

#### Form Requests

**ExecuteRequest**
- Validates execute endpoint input
- Ensures required fields
- Validates auth mode
- Checks parameter types

### 5. Frontend Architecture

Located in `resources/js/spectra/`, the React frontend uses Inertia.js for server-side routing.

#### Components

**EndpointTree**
- Displays route hierarchy
- Implements search (Cmd/Ctrl+K)
- Filters by method
- Selects endpoints

**RequestBuilder**
- Builds requests from schemas
- Generates forms dynamically
- Validates input
- Sends requests

**AuthPanel**
- Switches authentication modes
- Handles auth credentials
- Validates auth input

**ResponseViewer**
- Displays JSON responses
- Shows headers and cookies
- Provides copy functionality
- Tabs for different views

**CookiePanel**
- Lists cookies
- Shows decrypted values
- Refreshes on demand

**Collections**
- Saves/loads requests
- Exports/imports JSON
- Manages localStorage

**CodeEditor**
- Syntax highlighting
- JSON formatting
- Copy to clipboard

#### State Management

State is managed through React hooks:
- `useState` for local component state
- `useEffect` for side effects
- Props for parent-child communication

#### API Communication

Inertia.js handles communication:
- `router.get()` for fetching data
- `router.post()` for mutations
- Automatic CSRF handling

## Design Patterns

### 1. Command Pattern
`ExecuteCommand` encapsulates request execution as a command object.

### 2. Facade Pattern
Services provide simple interfaces to complex subsystems.

### 3. Repository Pattern
`RouteScanner` acts as a repository for route metadata.

### 4. DTO Pattern
Immutable DTOs ensure data integrity across boundaries.

### 5. Action Pattern
Single-purpose actions orchestrate business operations.

### 6. Resource Pattern
Resources transform data for API responses.

## Data Flow

### Route Discovery Flow
```
User loads /spectra
    → SpectraController::index()
        → DiscoverRoutesAction::execute()
            → RouteScanner::scanRoutes()
                → Returns RouteMeta[]
            → RouteResource::collection()
        → Inertia::render() with routes
    → React renders EndpointTree
```

### Request Execution Flow
```
User clicks "Send Request"
    → React calls /spectra/execute
        → ExecuteController::store()
            → ExecuteRequest validates input
            → ExecuteRequestAction::execute()
                → AuthBroker::authenticate()
                → RequestProxy::execute()
                    → Strips headers
                    → Executes via kernel
                    → Masks fields
                    → Returns ExecuteResult
            → ExecuteResultResource::make()
        → Returns JSON response
    → React updates ResponseViewer
```

### Schema Generation Flow
```
User selects endpoint
    → React calls /spectra/schema
        → SchemaController::show()
            → BuildSchemaAction::execute()
                → SchemaBuilder::build()
                    → Resolves FormRequest
                    → Converts rules
                    → Returns SchemaSpec
            → SchemaResource::make()
        → Returns JSON Schema
    → React generates form fields
```

## Security Architecture

### Defense in Depth

1. **Environment Checks**: Disabled outside local by default
2. **Gate Authorization**: `use-spectra` gate required
3. **Rate Limiting**: 60 requests/minute default
4. **Header Stripping**: Removes sensitive headers
5. **Field Masking**: Masks sensitive response data
6. **Internal Execution**: No external HTTP calls

### Request Flow Security

```
Request
    → Middleware: EnsureSpectraEnabled
        → Check enabled config
        → Check environment
        → Authorize gate
    → ExecuteRequest validation
    → Rate limiter
    → Header stripping
    → Execute
    → Field masking
    → Response
```

## Extension Points

### 1. Service Container
All services are bound and can be replaced:
```php
app()->bind(RouteScanner::class, CustomRouteScanner::class);
```

### 2. Schema Builder
Extend to add custom rules:
```php
app()->extend(SchemaBuilder::class, function ($builder) {
    // Add custom logic
    return $builder;
});
```

### 3. Middleware
Add custom middleware to routes:
```php
Route::middleware(['custom-middleware'])->group(/*...*/);
```

### 4. Gates
Override gate definition:
```php
Gate::define('use-spectra', function ($user) {
    return $user->hasCustomPermission();
});
```

## Performance Considerations

### 1. Route Caching
Routes are scanned once per request and cached by Laravel.

### 2. Schema Caching
Schemas can be cached to avoid repeated rule parsing.

### 3. Internal Execution
Requests execute internally, avoiding network overhead.

### 4. Lazy Loading
Frontend components are lazy-loaded for faster initial load.

## Testing Strategy

### 1. Unit Tests
Test individual services and actions in isolation.

### 2. Integration Tests
Test controller and action interactions.

### 3. Feature Tests
Test complete flows from HTTP to response.

### 4. Static Analysis
Larastan ensures type safety.

## Next Steps

- [Services](development/services.md) - Detailed service documentation
- [DTOs](development/dtos.md) - DTO usage and patterns
- [Extending Spectra](development/extending.md) - Customization guide
